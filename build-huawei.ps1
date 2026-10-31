# ==============================================================================
# Build Script for Huawei AppGallery (Package ID: com.amigagracia.app)
# ==============================================================================
# Usage in PowerShell from repository root:
#   .\build-huawei.ps1
#
# This script sets $env:APP_ID="com.amigagracia.app", builds both the release AAB
# and APK, and copies them to public\downloads\ ready for AppGallery Connect upload.
# ==============================================================================

$ErrorActionPreference = "Stop"

Write-Host "`n[1/4] Configuring package ID for Huawei AppGallery..." -ForegroundColor Cyan
$env:APP_ID = "com.amigagracia.app"

$originalDir = Get-Location
$flutterDir = Join-Path $PSScriptRoot "flutter_app"

try {
    Set-Location $flutterDir

    Write-Host "[2/4] Building Huawei AppBundle (.aab) with com.amigagracia.app..." -ForegroundColor Yellow
    flutter build appbundle --release

    Write-Host "[3/4] Building Huawei APK (.apk) with com.amigagracia.app..." -ForegroundColor Yellow
    flutter build apk --release

    Set-Location $originalDir

    Write-Host "[4/4] Copying release artifacts to public\downloads\..." -ForegroundColor Cyan
    $aabSource = Join-Path $flutterDir "build\app\outputs\bundle\release\app-release.aab"
    $apkSource = Join-Path $flutterDir "build\app\outputs\flutter-apk\app-release.apk"
    $downloadDir = Join-Path $PSScriptRoot "public\downloads"

    if (Test-Path $aabSource) {
        Copy-Item -Path $aabSource -Destination (Join-Path $downloadDir "huawei-release.aab") -Force
        Write-Host "  -> Successfully generated: public\downloads\huawei-release.aab" -ForegroundColor Green
    }

    if (Test-Path $apkSource) {
        Copy-Item -Path $apkSource -Destination (Join-Path $downloadDir "huawei-release.apk") -Force
        Write-Host "  -> Successfully generated: public\downloads\huawei-release.apk" -ForegroundColor Green
    }

    Write-Host "`nBUILD COMPLETE! Upload either 'huawei-release.aab' or 'huawei-release.apk' to Huawei AppGallery Connect.`n" -ForegroundColor Green
}
finally {
    # Clean up environment variable so subsequent builds don't accidentally inherit it
    Remove-Item Env:\APP_ID -ErrorAction SilentlyContinue
    Set-Location $originalDir
}
