# Silent Panel - Android Project

This is the main Android application folder containing all source code and configuration files.

## 📁 Folder Structure

```
silentpanel/
├── app/
│   ├── src/main/
│   │   ├── java/com/silentpanel/app/
│   │   │   ├── MainActivity.java                 [MODIFIED] Main activity with announcements
│   │   │   ├── WebsiteSelectorActivity.java    [MODIFIED] Dynamic panel loading
│   │   │   ├── ConfigManager.java               [NEW] Server communication
│   │   │   ├── Config.java                      [NEW] Configuration model
│   │   │   └── Panel.java                       [NEW] Panel data model
│   │   ├── res/
│   │   │   ├── layout/                          (UI layouts)
│   │   │   ├── drawable/                        (Images & icons)
│   │   │   └── values/                          (Strings, colors, dimens)
│   │   └── AndroidManifest.xml
│   └── build.gradle                             [MODIFIED] Added JSON dependency
├── build.gradle
├── settings.gradle
├── gradle.properties
└── README.md                                    (This file)
```

## 🆕 New Files Added

### ConfigManager.java
- Handles HTTP communication with backend server
- Fetches `/api/config` endpoint
- Parses JSON configuration
- Provides callback interface for async operations

### Config.java
- Data model for app configuration
- Contains: enabled, announcement, logo_url, app_title, panels list

### Panel.java
- Data model for individual panels
- Contains: name, url, site_key

## ✏️ Modified Files

### WebsiteSelectorActivity.java
- Changed from hardcoded buttons to dynamic button creation
- Fetches panel configuration from server
- Falls back to default panels if server unreachable
- Shows loading progress while fetching

### MainActivity.java
- Added announcement fetching and display
- Checks if server is enabled
- Shows alert dialog if server is disabled
- All original functionality preserved

### build.gradle
- Added: `implementation 'org.json:json:20230227'`
- For JSON parsing
- All other configurations unchanged

## 🔧 Setup Instructions

### Prerequisites
- Android Studio 2021.3 or later
- Android SDK API Level 21-34
- Gradle 7.0+

### Configuration

1. **Update Server URL**
   - Open `app/src/main/java/com/silentpanel/app/ConfigManager.java`
   - Change line ~16:
     ```java
     private static final String CONFIG_URL = "https://your-server.com/api/config";
     ```

2. **Add Internet Permission**
   - Open `app/src/main/AndroidManifest.xml`
   - Ensure this is present:
     ```xml
     <uses-permission android:name="android.permission.INTERNET" />
     ```

3. **Build APK**
   - In Android Studio: `Build → Build Bundle(s) / APK(s) → Build APK(s)`
   - Or CLI: `./gradlew assembleDebug`

## 🚀 How It Works

### On App Launch
1. WebsiteSelectorActivity starts
2. Fetches configuration from backend server
3. Dynamically creates buttons for each panel
4. Shows any announcements
5. User can click buttons to load websites

### Fetching from Server
```
GET http://your-server.com/api/config
```

Expected JSON Response:
```json
{
  "enabled": true,
  "announcement": "Welcome!",
  "logo_url": "",
  "app_title": "Silent Multi Panel",
  "panels": [
    {
      "name": "Panel Name",
      "url": "https://website.com",
      "site_key": "panel_key"
    }
  ]
}
```

## 📱 Features

✅ Dynamic panel loading from server
✅ No hardcoded URLs (easy to manage)
✅ Announcement support
✅ Server on/off control
✅ Fallback to default panels
✅ Credential auto-fill
✅ WebView for website loading
✅ Fullscreen and security features

## 🔒 Security

- Credentials stored locally (device-only, no cloud sync)
- FLAG_SECURE enabled (prevents screenshots)
- Fullscreen mode enabled
- HTTPS recommended for production server

## 📊 Build Configuration

- **Min SDK**: 21 (Android 5.0)
- **Target SDK**: 34 (Android 14)
- **Namespace**: com.silentpanel.app
- **64-bit Support**: Yes (armeabi-v7a, arm64-v8a)

## 🧪 Testing

### Test Local Server
```bash
# Point to local server during development
ConfigManager.java: CONFIG_URL = "http://10.0.2.2:5000/api/config"
```

### Debug Logs
```bash
adb logcat | grep ConfigManager
```

### Common Issues
- Verify internet permission in manifest
- Check server URL is correct
- Ensure backend server is running
- Monitor logcat for connection errors

## 📝 Integration with Backend

This Android app works with the backend system:
- **Backend Location**: `/backend/app.py`
- **Admin Dashboard**: http://localhost:5000/
- **API Endpoint**: GET /api/config

## 📚 Documentation

See root project documentation:
- `../README.md` - Project overview
- `../docs/INTEGRATION_GUIDE.md` - Detailed integration steps
- `../docs/ANDROID_MODIFICATIONS.md` - Technical changes
- `../backend/README.md` - Backend API documentation

## 🚀 Building for Production

1. Update server URL to production address
2. Update version code and name in build.gradle
3. Generate signed APK
4. Test thoroughly
5. Release to app store (if applicable)

## 📞 Support

For issues or questions, refer to:
- Android-specific: See this README
- Backend integration: See `../docs/INTEGRATION_GUIDE.md`
- API details: See `../backend/README.md`

---

**Ready to build? Start with updating the server URL in ConfigManager.java**
