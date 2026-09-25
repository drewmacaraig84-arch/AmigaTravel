// =============================================================================
// HmsEntryAbility.ets — ArkTS Native Entry for HarmonyOS
// Handles: HMS Push Kit registration, platform channels (MethodChannel +
//          EventChannel), local notifications, app icon badge, and topics.
//
// HMS App ID   : 6917615783422249990
// Sender ID    : 101653523864970318
// Package name : com.amiga.travel.flutter_app
// =============================================================================

import UIAbility from '@ohos.app.ability.UIAbility';
import Want from '@ohos.app.ability.Want';
import AbilityConstant from '@ohos.app.ability.AbilityConstant';
import window from '@ohos.window';
import notificationManager from '@ohos.notificationManager';
import badge from '@ohos.notificationBadge';
import { FlutterEngine, FlutterPlugin, MethodChannel, EventChannel } from '@ohos/flutter_ohos';
import { MethodCall, MethodResult } from '@ohos/flutter_ohos/src/main/ets/plugin/common/MethodChannel';
import pushService from '@hms.push.pushService';
import type { BusinessError } from '@ohos.base';

// ── Constants ─────────────────────────────────────────────────────────────────
const METHOD_CHANNEL = 'com.amiga.travel.flutter_app/hms_push';
const EVENT_CHANNEL = 'com.amiga.travel.flutter_app/hms_push_events';
const HMS_APP_ID = '6917615783422249990';

// ── Module-level event sink (set when Flutter listens on EventChannel) ────────
let globalEventSink: ((event: Object) => void) | null = null;

// ── HmsPushPlugin ─────────────────────────────────────────────────────────────
class HmsPushPlugin implements FlutterPlugin {
  private methodChannel: MethodChannel | null = null;
  private eventChannel: EventChannel | null = null;
  private pendingInitialMessage: Record<string, string> | null = null;

  onAttachedToEngine(binding: FlutterPlugin.FlutterPluginBinding): void {
    this.methodChannel = new MethodChannel(
      binding.getBinaryMessenger(),
      METHOD_CHANNEL
    );
    this.methodChannel.setMethodCallHandler(this.handleMethod.bind(this));

    this.eventChannel = new EventChannel(
      binding.getBinaryMessenger(),
      EVENT_CHANNEL
    );
    this.eventChannel.setStreamHandler({
      onListen: (_args: Object, sink: (event: Object) => void) => {
        globalEventSink = sink;
      },
      onCancel: (_args: Object) => {
        globalEventSink = null;
      },
    });
  }

  onDetachedFromEngine(_binding: FlutterPlugin.FlutterPluginBinding): void {
    this.methodChannel?.setMethodCallHandler(null);
    this.eventChannel?.setStreamHandler(null);
    globalEventSink = null;
  }

  private async handleMethod(call: MethodCall, result: MethodResult): Promise<void> {
    try {
      switch (call.method) {
        case 'initialize': {
          // Request HMS Push token
          const tokenResult = await pushService.getToken(HMS_APP_ID);
          const token: string = tokenResult.token ?? '';
          console.info(`[HMS] Push token obtained: ${token}`);

          // Subscribe to global topic
          await pushService.subscribe(HMS_APP_ID, 'all_users');

          // Return the push token to Dart
          result.success(token);

          // Deliver any pending cold-start message after token is set
          if (this.pendingInitialMessage) {
            this.methodChannel?.invokeMethod('onInitialMessage', this.pendingInitialMessage);
            this.pendingInitialMessage = null;
          }
          break;
        }

        case 'getInitialMessage': {
          result.success(this.pendingInitialMessage);
          this.pendingInitialMessage = null;
          break;
        }

        case 'requestPermission': {
          await notificationManager.requestEnableNotification();
          result.success(null);
          break;
        }

        case 'showNotification': {
          const id: number = (call.args as Record<string, number>)['id'] ?? 0;
          const title: string = (call.args as Record<string, string>)['title'] ?? '';
          const body: string = (call.args as Record<string, string>)['body'] ?? '';
          const payload: string = (call.args as Record<string, string>)['payload'] ?? '';

          const request: notificationManager.NotificationRequest = {
            id,
            content: {
              notificationContentType: notificationManager.ContentType.NOTIFICATION_CONTENT_BASIC_TEXT,
              normal: {
                title,
                text: body,
                additionalText: payload,
              },
            },
          };
          await notificationManager.publish(request);
          result.success(null);
          break;
        }

        case 'setBadge': {
          const count: number = (call.args as Record<string, number>)['count'] ?? 0;
          await badge.setBadgeNumber(count);
          result.success(null);
          break;
        }

        case 'subscribeToTopic': {
          const topic: string = (call.args as Record<string, string>)['topic'] ?? '';
          if (topic.length > 0) {
            await pushService.subscribe(HMS_APP_ID, topic);
          }
          result.success(null);
          break;
        }

        case 'unsubscribeFromTopic': {
          const topic: string = (call.args as Record<string, string>)['topic'] ?? '';
          if (topic.length > 0) {
            await pushService.unsubscribe(HMS_APP_ID, topic);
          }
          result.success(null);
          break;
        }

        default:
          result.notImplemented();
      }
    } catch (err) {
      const e = err as BusinessError;
      result.error('HMS_ERROR', e.message ?? 'Unknown error', null);
    }
  }

  // Called by HmsEntryAbility when a push message arrives while app is running
  public deliverMessage(data: Record<string, string>): void {
    if (globalEventSink) {
      globalEventSink({
        event_type: 'message',
        title: data['title'] ?? '',
        body: data['body'] ?? '',
        data: data,
      });
    }
  }

  // Called when user taps a push notification to open the app
  public deliverNotificationTap(payload: string): void {
    if (globalEventSink) {
      globalEventSink({
        event_type: 'notification_tap',
        payload,
      });
    }
  }

  // Store cold-start payload (app was terminated when notification arrived)
  public setInitialMessage(data: Record<string, string>): void {
    this.pendingInitialMessage = data;
  }
}

// ── Singleton plugin instance accessible to the Ability ──────────────────────
let hmsPushPlugin: HmsPushPlugin | null = null;

// ── HmsEntryAbility ────────────────────────────────────────────────────────────
export default class HmsEntryAbility extends UIAbility {
  private flutterEngine: FlutterEngine | null = null;

  onCreate(want: Want, launchParam: AbilityConstant.LaunchParam): void {
    // Check if launched by a push notification tap (cold start)
    const pushData = want?.parameters?.['pushPayload'];
    if (pushData) {
      const plugin = this.getOrCreatePlugin();
      plugin.setInitialMessage(
        typeof pushData === 'string'
          ? JSON.parse(pushData) as Record<string, string>
          : pushData as Record<string, string>
      );
    }

    // Create and configure the Flutter engine
    this.flutterEngine = new FlutterEngine(this.context);
    hmsPushPlugin = this.getOrCreatePlugin();
    this.flutterEngine.getPlugins().add(hmsPushPlugin);
    this.flutterEngine.getDartExecutor().executeDartEntrypoint(
      { libraryUri: '', entrypointFunctionName: 'main' }
    );
  }

  onDestroy(): void {
    this.flutterEngine?.destroy();
  }

  onWindowStageCreate(windowStage: window.WindowStage): void {
    windowStage.loadContent('pages/Index', (err) => {
      if (err.code) {
        console.error(`[HMS] loadContent failed: ${JSON.stringify(err)}`);
      }
    });
  }

  // ── HMS Push receiver (called by system when push arrives) ─────────────────
  // Register this class as the push receiver in module.json5:
  //   "pushReceiver": "HmsEntryAbility"
  onReceiveMessage(message: Record<string, string>): void {
    const title = message['title'] ?? 'Amiga Travel';
    const body = message['body'] ?? '';
    const payload = JSON.stringify(message);

    if (this.flutterEngine) {
      // App is running — forward to Flutter via EventChannel
      hmsPushPlugin?.deliverMessage(message);
    } else {
      // App is not running — show a local notification directly
      const request: notificationManager.NotificationRequest = {
        id: Date.now(),
        content: {
          notificationContentType: notificationManager.ContentType.NOTIFICATION_CONTENT_BASIC_TEXT,
          normal: { title, text: body, additionalText: payload },
        },
      };
      notificationManager.publish(request).catch((err: BusinessError) => {
        console.error(`[HMS] Background notification error: ${err.message}`);
      });
    }
  }

  private getOrCreatePlugin(): HmsPushPlugin {
    if (!hmsPushPlugin) {
      hmsPushPlugin = new HmsPushPlugin();
    }
    return hmsPushPlugin;
  }
}
