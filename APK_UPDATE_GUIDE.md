# How to Update the Android App (APK)

When you make changes to the Flutter app and want to push an update to your users, follow these exact steps. The system is designed to automatically detect your new version and force existing users to update.

### Step 1: Bump the Version Number

You need to update the version number in two places:

1. **`flutter_app/pubspec.yaml`**
   Find the `version:` line near the top and increment it.
   ```yaml
   # Example: Change from 1.0.1+2 to 1.0.2+3
   version: 1.0.2+3
   ```

2. **`flutter_app/lib/main.dart`**
   Find the `appVersion` constant (around line 45) and make sure it matches the new version exactly.
   ```dart
   // Example: Change from '1.0.1+2' to '1.0.2+3'
   static const String appVersion = '1.0.2+3';
   ```

### Step 2: Build the New Packages

#### A. For Website Direct Download & Google Play Store
Package ID: `com.amiga.travel.flutter_app` (Default)

```powershell
cd flutter_app
flutter build apk --release
# or for Play Store:
flutter build appbundle --release
```

Copy the APK to your website downloads folder:
```powershell
Copy-Item "flutter_app\build\app\outputs\flutter-apk\app-release.apk" -Destination "public\downloads\amiga-travel.apk" -Force
```

---

#### B. For Huawei AppGallery
Package ID: `com.amigagracia.app`

Huawei AppGallery requires the registered package ID `com.amigagracia.app`. To build for Huawei, run the automated script from the project root:

```powershell
.\build-huawei.ps1
```

*Or manually in PowerShell:*
```powershell
cd flutter_app
$env:APP_ID = "com.amigagracia.app"
flutter build appbundle --release
flutter build apk --release
Remove-Item Env:\APP_ID
```

The output files are generated in:
- App Bundle: `public\downloads\huawei-release.aab`
- APK: `public\downloads\huawei-release.apk`

---

### Step 3: Deploy or Upload to App Stores

- **Website Direct APK:** Commit and push `public/downloads/amiga-travel.apk` to production.
- **Huawei AppGallery Connect:** Go to AppGallery Connect > App Services > Version Information / Manage packages, and upload `huawei-release.aab` (or `huawei-release.apk`).
- **Google Play Console:** Upload `flutter_app/build/app/outputs/bundle/release/app-release.aab`.

---

### What happens automatically next?

Once you complete Step 3 and push your changes to your live server (Vercel/Railway), the system handles the rest dynamically:

- 🪄 **The Website Updates:** The download page will automatically read the new version from `pubspec.yaml` and recalculate the new APK file size.
- 🪄 **Users are Forced to Update:** Anyone who opens an older version of the app will hit the `/api/app-version` check, see that a new version exists, and receive an update prompt routing them to their specific store (Play Store, AppGallery, or Website APK).
