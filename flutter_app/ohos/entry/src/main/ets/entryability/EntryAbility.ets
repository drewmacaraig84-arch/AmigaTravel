// =============================================================================
// EntryAbility.ets — Flutter + HMS Push Kit Entry for HarmonyOS
// Extends FlutterAbility (the correct OHOS Flutter base class).
// Adds HMS Push Kit via MethodChannel + EventChannel on top of standard Flutter.
//
// HMS App ID   : 6917615783422249990
// Sender ID    : 101653523864970318
// Package name : com.amiga.travel.flutter_app
// =============================================================================

import { FlutterAbility, FlutterEngine } from '@ohos/flutter_ohos';
import { GeneratedPluginRegistrant } from '../plugins/GeneratedPluginRegistrant';
import Want from '@ohos.app.ability.Want';
import AbilityConstant from '@ohos.app.ability.AbilityConstant';
import notificationManager from '@ohos.notificationManager';
import badge from '@ohos.notificationBadge';
import { MethodChannel, EventChannel } from '@ohos/flutter_ohos';
import { MethodCall, MethodResult } from '@ohos/flutter_ohos/src/main/ets/plugin/common/MethodChannel';
import pushService from '@hms.push.pushService';
import type { BusinessError } from '@ohos.base';

// ── Constants ─────────────────────────────────────────────────────────────────
const METHOD_CHANNEL = 'com.amiga.travel.flutter_app/hms_push';
const EVENT_CHANNEL  = 'com.amiga.travel.flutter_app/hms_push_events';
const HMS_APP_ID     = '6917615783422249990';

// ── Module-level event sink ───────────────────────────────────────────────────
let globalEventSink: ((event: Object) => void) | null = null;

// ── Pending cold-start notification payload ───────────────────────────────────
let pendingInitialMessage: Record<string, string> | null = null;

export default class EntryAbility extends FlutterAbility {

  // ── Called when Flutter engine is created — register all plugins ──────────
  configureFlutterEngine(flutterEngine: FlutterEngine): void {
    super.configureFlutterEngine(flutterEngine);

    // 1. Register all auto-generated Flutter plugins (http, shared_prefs, etc.)
    GeneratedPluginRegistrant.registerWith(flutterEngine);

    // 2. Register HMS Push MethodChannel
    const methodChannel = new MethodChannel(
      flutterEngine.getDartExecutor().getBinaryMessenger(),
      METHOD_CHANNEL
    );
    methodChannel.setMethodCallHandler(
      async (call: MethodCall, result: MethodResult) => {
        try {
          switch (call.method) {

            case 'initialize': {
              const tokenResult = await pushService.getToken(HMS_APP_ID);
              const token: string = tokenResult.token ?? '';
              await pushService.subscribe(HMS_APP_ID, 'all_users');
              result.success(token);
              break;
            }

            case 'getInitialMessage': {
              result.success(pendingInitialMessage);
              pendingInitialMessage = null;
              break;
            }

            case 'requestPermission': {
              await notificationManager.requestEnableNotification();
              result.success(null);
              break;
            }

            case 'showNotification': {
              const args = call.args as Record<string, unknown>;
              const request: notificationManager.NotificationRequest = {
                id: args['id'] as number ?? 0,
                content: {
                  notificationContentType:
                    notificationManager.ContentType.NOTIFICATION_CONTENT_BASIC_TEXT,
                  normal: {
                    title: args['title'] as string ?? '',
                    text:  args['body']  as string ?? '',
                    additionalText: args['payload'] as string ?? '',
                  },
                },
              };
              await notificationManager.publish(request);
              result.success(null);
              break;
            }

            case 'setBadge': {
              const count = (call.args as Record<string, number>)['count'] ?? 0;
              await badge.setBadgeNumber(count);
              result.success(null);
              break;
            }

            case 'subscribeToTopic': {
              const topic = (call.args as Record<string, string>)['topic'] ?? '';
              if (topic) await pushService.subscribe(HMS_APP_ID, topic);
              result.success(null);
              break;
            }

            case 'unsubscribeFromTopic': {
              const topic = (call.args as Record<string, string>)['topic'] ?? '';
              if (topic) await pushService.unsubscribe(HMS_APP_ID, topic);
              result.success(null);
              break;
            }

            default:
              result.notImplemented();
          }
        } catch (err) {
          const e = err as BusinessError;
          result.error('HMS_ERROR', e.message ?? 'Unknown', null);
        }
      }
    );

    // 3. Register HMS Push EventChannel (foreground messages + tap events)
    const eventChannel = new EventChannel(
      flutterEngine.getDartExecutor().getBinaryMessenger(),
      EVENT_CHANNEL
    );
    eventChannel.setStreamHandler({
      onListen: (_args: Object, sink: (event: Object) => void) => {
        globalEventSink = sink;
      },
      onCancel: (_args: Object) => {
        globalEventSink = null;
      },
    });
  }

  // ── Called when ability is created (check cold-start push payload) ─────────
  onCreate(want: Want, launchParam: AbilityConstant.LaunchParam): void {
    super.onCreate(want, launchParam);
    const pushData = want?.parameters?.['pushPayload'];
    if (pushData) {
      pendingInitialMessage = typeof pushData === 'string'
        ? JSON.parse(pushData) as Record<string, string>
        : pushData as Record<string, string>;
    }
  }

  // ── HMS Push receiver — called by system when a push message arrives ───────
  onReceiveMessage(message: Record<string, string>): void {
    const title   = message['title']   ?? 'Amiga Travel';
    const body    = message['body']    ?? '';
    const payload = JSON.stringify(message);

    if (globalEventSink) {
      // App is in foreground — send to Flutter via EventChannel
      globalEventSink({
        event_type: 'message',
        title,
        body,
        data: message,
      });
    } else {
      // App is in background — show system notification directly
      const request: notificationManager.NotificationRequest = {
        id: Date.now(),
        content: {
          notificationContentType:
            notificationManager.ContentType.NOTIFICATION_CONTENT_BASIC_TEXT,
          normal: { title, text: body, additionalText: payload },
        },
      };
      notificationManager.publish(request).catch((err: BusinessError) => {
        console.error(`[HMS] Background notification error: ${err.message}`);
      });
    }
  }

  // ── Called when user taps push notification while app is backgrounded ──────
  onNewWant(want: Want, launchParam: AbilityConstant.LaunchParam): void {
    super.onNewWant(want, launchParam);
    const pushData = want?.parameters?.['pushPayload'];
    if (pushData && globalEventSink) {
      globalEventSink({
        event_type: 'notification_tap',
        payload: typeof pushData === 'string' ? pushData : JSON.stringify(pushData),
      });
    }
  }
}
