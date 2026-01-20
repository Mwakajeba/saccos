# SmartApp - Mobile Application

## Adding Logo to Login Page

This document provides a step-by-step guide for adding a logo to the login page of the SmartApp Flutter application.

### Overview

The login page displays a logo image from `assets/icon/logo.png` in a square container with rounded corners and a semi-transparent white background.

### Prerequisites

- Flutter SDK installed (version 3.5.3 or higher)
- Android Studio / Xcode (for building)
- Logo image file (`logo.png`) placed in `assets/icon/` directory

### Step-by-Step Instructions

#### Step 1: Prepare the Logo Image

1. Ensure your logo image is named `logo.png`
2. Place the logo file in: `mobileapp/saccosapp/assets/icon/logo.png`
3. Recommended logo dimensions: 512x512 pixels or higher (for best quality)
4. Supported formats: PNG (recommended), JPG

#### Step 2: Add Logo to Assets

1. Open `pubspec.yaml` file
2. Locate the `assets` section under `flutter:`
3. Add the logo path to the assets list:

```yaml
flutter:
  uses-material-design: true
  
  assets:
    - assets/icon/app_icon.png
    - assets/icon/logo.png  # Add this line
```

#### Step 3: Update Login Page Code

1. Open `lib/login_page.dart`
2. Locate the `_buildHeroSection()` method (around line 153)
3. Update the logo container code:

```dart
Widget _buildHeroSection() {
  return Column(
    children: [
      Container(
        width: 150,
        height: 150,
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.5),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Image.asset(
            'assets/icon/logo.png',
            width: 126,
            height: 126,
            fit: BoxFit.contain,
          ),
        ),
      ),
      // ... rest of the code
    ],
  );
}
```

#### Step 4: Run Flutter Commands

After making the changes, run the following commands in your terminal:

```bash
cd mobileapp/saccosapp
flutter pub get
```

This will register the new asset in your Flutter project.

### Logo Container Configuration

The logo is displayed with the following specifications:

- **Container Size**: 150x150 pixels
- **Container Shape**: Square with rounded corners (16px radius)
- **Background Color**: White with 50% opacity (`Colors.white.withOpacity(0.5)`)
- **Image Size**: 126x126 pixels (with 12px padding on all sides)
- **Image Fit**: `BoxFit.contain` (preserves aspect ratio)

### Customization Options

You can customize the logo appearance by modifying the following properties:

#### Change Logo Size

```dart
Container(
  width: 200,  // Change container width
  height: 200, // Change container height
  // ...
  child: Image.asset(
    'assets/icon/logo.png',
    width: 176,  // Adjust image width (container width - 24)
    height: 176, // Adjust image height (container height - 24)
    // ...
  ),
)
```

#### Change Background Opacity

```dart
decoration: BoxDecoration(
  color: Colors.white.withOpacity(0.8), // Increase opacity (0.0 to 1.0)
  borderRadius: BorderRadius.circular(16),
),
```

#### Change Border Radius (More/Less Rounded)

```dart
decoration: BoxDecoration(
  color: Colors.white.withOpacity(0.5),
  borderRadius: BorderRadius.circular(8), // Smaller = less rounded
  // or
  borderRadius: BorderRadius.circular(30), // Larger = more rounded
),
```

#### Make Logo Circular

```dart
Container(
  width: 150,
  height: 150,
  decoration: BoxDecoration(
    color: Colors.white.withOpacity(0.5),
    shape: BoxShape.circle, // Use circle instead of rounded square
  ),
  child: ClipOval( // Clip image to circle
    child: Padding(
      padding: const EdgeInsets.all(12),
      child: Image.asset(
        'assets/icon/logo.png',
        width: 126,
        height: 126,
        fit: BoxFit.cover, // Use cover for circular logos
      ),
    ),
  ),
)
```

### App Configuration

#### App Name Configuration

The app is configured to display as "smartapp" when installed:

**Files Modified:**
1. `pubspec.yaml` - Package name: `smartapp`
2. `android/app/src/main/AndroidManifest.xml` - Android label: `smartapp`
3. `ios/Runner/Info.plist` - iOS display name: `Smartapp`

#### App Icon Configuration

The app uses `app_icon.png` as the launcher icon:

**Configuration in `pubspec.yaml`:**
```yaml
flutter_launcher_icons:
  android: true
  ios: true
  image_path: "assets/icon/app_icon.png"
```

**To generate app icons:**
```bash
flutter pub run flutter_launcher_icons
```

### Building the App

#### For Android

1. **Generate app icons** (if not done already):
   ```bash
   flutter pub run flutter_launcher_icons
   ```

2. **Build APK**:
   ```bash
   flutter build apk
   ```
   Output: `build/app/outputs/flutter-apk/app-release.apk`

3. **Build App Bundle** (for Google Play Store):
   ```bash
   flutter build appbundle
   ```
   Output: `build/app/outputs/bundle/release/app-release.aab`

#### For iOS

1. **Generate app icons** (if not done already):
   ```bash
   flutter pub run flutter_launcher_icons
   ```

2. **Build iOS app**:
   ```bash
   flutter build ios
   ```

3. **Open in Xcode** for further configuration and signing:
   ```bash
   open ios/Runner.xcworkspace
   ```

### Troubleshooting

#### Logo Not Appearing

1. **Check asset path**: Ensure `logo.png` exists in `assets/icon/logo.png`
2. **Run `flutter pub get`**: This registers new assets
3. **Hot restart**: Use `R` in terminal or restart the app completely
4. **Check console**: Look for asset loading errors in Flutter console

#### Logo Appears Blurry

1. **Use higher resolution**: Ensure logo.png is at least 512x512 pixels
2. **Check image format**: Use PNG format for best quality
3. **Adjust fit property**: Try `BoxFit.cover` or `BoxFit.fill` instead of `BoxFit.contain`

#### Build Errors

1. **Clean build**: Run `flutter clean` then `flutter pub get`
2. **Check dependencies**: Ensure all packages are up to date
3. **Verify asset paths**: Double-check all asset paths in `pubspec.yaml`

### File Structure

```
mobileapp/saccosapp/
├── assets/
│   └── icon/
│       ├── app_icon.png    # App launcher icon
│       └── logo.png        # Login page logo
├── lib/
│   └── login_page.dart     # Login page with logo
├── android/
│   └── app/
│       └── src/
│           └── main/
│               └── AndroidManifest.xml  # Android app name
├── ios/
│   └── Runner/
│       └── Info.plist      # iOS app name
└── pubspec.yaml            # App configuration & assets
```

### Summary of Changes

1. ✅ Added `logo.png` to assets in `pubspec.yaml`
2. ✅ Updated `login_page.dart` to display logo in square container
3. ✅ Configured logo with white background and rounded corners
4. ✅ Changed app name from "visionapp" to "smartapp"
5. ✅ Configured app icon to use `app_icon.png`

### Additional Resources

- [Flutter Asset Documentation](https://docs.flutter.dev/development/ui/assets-and-images)
- [Flutter Launcher Icons Package](https://pub.dev/packages/flutter_launcher_icons)
- [Flutter Build Documentation](https://docs.flutter.dev/deployment/android)
- [Flutter iOS Deployment](https://docs.flutter.dev/deployment/ios)

---

**Last Updated**: January 2025
**App Version**: 1.0.0+1
**Flutter SDK**: ^3.5.3
