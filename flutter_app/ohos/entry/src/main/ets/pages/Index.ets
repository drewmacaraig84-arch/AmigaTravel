// Main Flutter page wrapper for HarmonyOS
// This ArkTS page hosts the Flutter UI surface (FlutterPage component)

import { FlutterPage } from '@ohos/flutter_ohos';

@Entry
@Component
struct Index {
  build() {
    Column() {
      FlutterPage({ viewId: 'FlutterView' })
        .width('100%')
        .height('100%')
    }
    .width('100%')
    .height('100%')
    .backgroundColor('#000000')
  }
}
