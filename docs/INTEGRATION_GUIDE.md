# Android App Integration Guide

## Quick Summary

Your Android app has been modified to fetch configuration from a **backend server** instead of having hardcoded button URLs. This allows you to control everything remotely without rebuilding the APK.

## What Changed in the Android App

### New Files to Add to Your Project:
1. `ConfigManager.java` - Handles server communication
2. `Config.java` - Data model for configuration
3. `Panel.java` - Data model for individual panels

### Modified Files:
1. `WebsiteSelectorActivity.java` - Now fetches panels from server dynamically
2. `MainActivity.java` - Now checks for announcements and server status
3. `build.gradle` - Added JSON dependency

### Also Update:
- **AndroidManifest.xml** - Ensure INTERNET permission exists:
  ```xml
  <uses-permission android:name="android.permission.INTERNET" />
  ```

## Integration Steps

### Step 1: Copy New Files
Copy these 3 new Java files to `app/src/main/java/com/silentpanel/app/`:
- ConfigManager.java
- Config.java
- Panel.java

### Step 2: Replace Files
Replace these files in your project:
- WebsiteSelectorActivity.java
- MainActivity.java
- build.gradle

### Step 3: Update API URL
In `ConfigManager.java`, change line ~16:
```java
private static final String CONFIG_URL = "https://your-backend-api.com/api/config";
```
Replace with your actual backend server URL.

### Step 4: Add Internet Permission
In `AndroidManifest.xml`, ensure this is present in the `<manifest>` section:
```xml
<uses-permission android:name="android.permission.INTERNET" />
```

### Step 5: Update Gradle
In `build.gradle` (app level), the dependencies section now includes:
```gradle
dependencies {
    implementation 'org.json:json:20230227'
}
```

## Backend Server API Requirements

Your backend server needs a single endpoint:

### Endpoint: `GET /api/config`

**Full URL:** `https://your-backend-api.com/api/config`

**Response Format (JSON):**
```json
{
  "enabled": true,
  "announcement": "Welcome to Silent Panel!",
  "logo_url": "https://your-server.com/logo.png",
  "app_title": "Silent Multi Panel",
  "panels": [
    {
      "name": "Button Display Name",
      "url": "https://website-to-load.com",
      "site_key": "unique_key_for_credentials"
    }
  ]
}
```

### Response Field Descriptions:
- **enabled** (boolean): Set to `false` to disable the app (show message and close)
- **announcement** (string): Message shown to users (empty = no message)
- **logo_url** (string): URL to app logo (future enhancement)
- **app_title** (string): Custom app title (future enhancement)
- **panels** (array): List of buttons/panels to display

## Control Flow

```
App Starts
    ↓
Fetch Config from Server (GET /api/config)
    ↓
Server Returns JSON
    ↓
Is server enabled? → NO → Show message, close app
    ↓ YES
    ↓
Show announcement (if any)
    ↓
Dynamically create buttons from panels array
    ↓
User clicks button → Load website
```

## Fallback Behavior

If the server is unreachable (network error, timeout, 404, etc.):
- App automatically falls back to **default hardcoded panels**
- Shows error toast message
- Users can still use the app

This ensures the app always works, even if your backend server is down.

## What You Can Control from the Backend

Once your backend server is set up, you can control:

✅ **Button Names** - Change what text appears on each button
✅ **Button URLs** - Change which websites load when clicked  
✅ **Add/Remove Buttons** - Add new panels or remove old ones
✅ **Enable/Disable App** - Turn the entire app on/off for maintenance
✅ **Send Announcements** - Show messages to all users
✅ **Logo & Branding** - Set custom app title and logo URL (future)

## Next Steps

1. Build your backend server API (`/api/config` endpoint)
2. Integrate the modified Android code
3. Rebuild the APK
4. Test with your backend server
5. Deploy backend server to production
6. Update ConfigManager.java with production URL

## Example Backend Response

When a user opens the app, they receive this configuration:

```json
{
  "enabled": true,
  "announcement": "🎉 New panels added! Check them out.",
  "logo_url": "https://api.example.com/logo.png",
  "app_title": "Multi Panel Manager",
  "panels": [
    {
      "name": "Gaming Panel 1",
      "url": "https://gaming.example.com",
      "site_key": "gaming1"
    },
    {
      "name": "Gaming Panel 2",
      "url": "https://gaming2.example.com",
      "site_key": "gaming2"
    },
    {
      "name": "Support Panel",
      "url": "https://support.example.com",
      "site_key": "support"
    }
  ]
}
```

When you want to disable the app for maintenance:

```json
{
  "enabled": false,
  "announcement": "Maintenance in progress. App will be available in 2 hours.",
  "panels": []
}
```

---

**Ready for the next step?** Once this is integrated and tested, we can build the **control dashboard website** to manage all these settings from a web interface.
