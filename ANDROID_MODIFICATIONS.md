# Android App Modifications for Server Configuration

## Overview
The Android app has been modified to fetch panel configuration dynamically from a backend server instead of hardcoding button URLs and names.

## Modified Files

### 1. **ConfigManager.java** (NEW)
- Handles all HTTP communication with the backend server
- Fetches configuration JSON from the server
- Parses JSON into Config and Panel objects
- Uses callback pattern for asynchronous operations
- Includes error handling and timeout management

**Configuration URL:** `https://your-backend-api.com/api/config`

### 2. **Config.java** (NEW)
Data model for server configuration containing:
- `serverEnabled` - Boolean flag to enable/disable the app
- `announcement` - Message to display to all users
- `logoUrl` - URL to display custom logo
- `appTitle` - Custom app title
- `panels` - List of Panel objects

### 3. **Panel.java** (NEW)
Data model for individual panel containing:
- `name` - Button display name
- `url` - Website URL to load
- `siteKey` - Key for credential storage

### 4. **WebsiteSelectorActivity.java** (MODIFIED)
**Changes:**
- Removed hardcoded button bindings
- Now fetches configuration from server on startup
- Dynamically creates buttons based on server configuration
- Displays announcements from server
- Shows loading progress while fetching config
- Falls back to default hardcoded panels if server fails
- Shows alert if server is disabled

### 5. **MainActivity.java** (MODIFIED)
**Changes:**
- Added server announcement fetching
- Checks if server is enabled, closes app if disabled
- Shows announcements from server
- All existing functionality (auto-login, credential capture) preserved

### 6. **build.gradle** (MODIFIED)
- Added `org.json:json` dependency for JSON parsing

## Server API Endpoint

The app expects a backend server with this endpoint:

```
GET https://your-backend-api.com/api/config
```

### Expected JSON Response Format:

```json
{
  "enabled": true,
  "announcement": "Welcome to Silent Panel! New features coming soon.",
  "logo_url": "https://your-server.com/logo.png",
  "app_title": "Silent Multi Panel",
  "panels": [
    {
      "name": "Panel Name",
      "url": "https://panel.example.com",
      "site_key": "unique_panel_key"
    },
    {
      "name": "Another Panel",
      "url": "https://another-panel.example.com",
      "site_key": "another_key"
    }
  ]
}
```

## Features

### ✅ Dynamic Panel Management
- Add/remove panels from the control dashboard
- Change button names and URLs
- All changes reflected in real-time without app update

### ✅ Server Control
- Enable/disable the entire app from the dashboard
- App shows message and closes if server is disabled
- Useful for maintenance or emergency shutdown

### ✅ Announcements
- Send messages to all users
- Displayed on both selector and main activity
- Can announce new features, maintenance, etc.

### ✅ Fallback Mechanism
- If server is unreachable, app uses default hardcoded panels
- Ensures app always remains functional
- Graceful degradation if backend is down

### ✅ Android Compatibility
- Min SDK: 21 (Android 5.0)
- Target SDK: 34 (Android 14)
- Uses built-in HttpURLConnection (no external HTTP library needed)
- JSON parsing via org.json library

## Implementation Steps

1. **Update Configuration URL:**
   In `ConfigManager.java`, change `CONFIG_URL` to your backend server:
   ```java
   private static final String CONFIG_URL = "https://your-backend-api.com/api/config";
   ```

2. **Create Backend Server:**
   Build a web server that provides the `/api/config` endpoint with the JSON format above

3. **Add to AndroidManifest.xml:**
   Ensure these permissions are present:
   ```xml
   <uses-permission android:name="android.permission.INTERNET" />
   <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
   ```

4. **Rebuild APK:**
   Compile and test with your backend server running

## Security Considerations

- All communication uses standard HTTP/HTTPS
- Credentials are still stored locally (device-only)
- Consider using HTTPS for production
- Can add API key authentication if needed
- Server should validate all data before accepting

## Future Enhancements

- Add logo/branding support
- Image loading from server
- Push notifications for announcements
- Version checking and forced updates
- Custom themes/colors from server
