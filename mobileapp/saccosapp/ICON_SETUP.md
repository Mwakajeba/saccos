# App Icon Setup Instructions

## Current Status
✅ flutter_launcher_icons package installed
✅ Configuration added to pubspec.yaml
✅ assets/icon directory created

## To Complete Setup:

1. **Add your icon image:**
   - Place your PNG icon file (1024x1024px recommended) at:
     ```
     assets/icon/app_icon.png
     ```

2. **Generate the icons:**
   ```bash
   flutter pub run flutter_launcher_icons
   ```

3. **Build your app:**
   ```bash
   # For Android APK
   flutter build apk --release
   
   # For Linux
   flutter build linux --release
   ```

## Customization Options

If you want adaptive icons for Android (different background/foreground), uncomment and customize in `pubspec.yaml`:

```yaml
flutter_launcher_icons:
  android: true
  ios: true
  image_path: "assets/icon/app_icon.png"
  
  # Adaptive icon (Android 8.0+)
  adaptive_icon_background: "#13EC5B"  # Your brand color
  adaptive_icon_foreground: "assets/icon/app_icon_foreground.png"
```

## Notes
- The icon will replace the default Flutter icon on all platforms
- Android will generate multiple sizes automatically (48dp to 192dp)
- iOS will generate all required sizes
- Run the generation command each time you update the icon
