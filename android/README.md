# Silent Panel - Android App Integration

Modified Android app files to fetch panel configuration from a remote server.

## 📁 Files in This Folder

### New Files (Add to Your Project)
1. **ConfigManager.java** - Handles HTTP communication with backend
2. **Config.java** - Configuration data model
3. **Panel.java** - Individual panel data model

### Modified Files (Replace in Your Project)
1. **WebsiteSelectorActivity.java** - Loads panels from server dynamically
2. **MainActivity.java** - Shows announcements and checks server status
3. **build.gradle** - Added JSON parsing dependency

## 🔧 Integration Steps

### Step 1: Copy Files to Your Project

1. Copy these files to `app/src/main/java/com/silentpanel/app/`:
   - ConfigManager.java
   - Config.java
   - Panel.java
   - WebsiteSelectorActivity.java (replace existing)
   - MainActivity.java (replace existing)

### Step 2: Update build.gradle

Replace your `app/build.gradle` with the provided version, or add to dependencies:
```gradle
dependencies {
    implementation 'org.json:json:20230227'
}
```

### Step 3: Update ConfigManager.java

Open `ConfigManager.java` and change the API URL (line ~16):

**Before:**
```java
private static final String CONFIG_URL = "https://your-backend-api.com/api/config";
```

**After:**
```java
private static final String CONFIG_URL = "https://your-actual-server.com/api/config";
```

### Step 4: Ensure Android Permissions

Make sure your `AndroidManifest.xml` has these permissions:
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

### Step 5: Rebuild APK

1. In Android Studio: `Build → Build Bundle(s) / APK(s) → Build APK(s)`
2. Or use command line: `./gradlew assembleDebug`
3. Test on device or emulator

## 🎯 How It Works

### On App Launch

```
1. WebsiteSelectorActivity starts
2. Shows loading progress bar
3. Fetches /api/config from server
4. Receives JSON with panels
5. Dynamically creates buttons
6. Shows any announcements
```

### When User Clicks Button

```
1. Button click detected
2. Passes URL to MainActivity
3. MainActivity loads URL in WebView
4. Auto-login credentials captured
5. Website loads normally
```

### If Server is Unreachable

```
1. Connection fails
2. App falls back to default panels
3. Shows toast notification
4. App still works!
```

## 📋 Required Config Endpoint

Your backend must provide: `GET /api/config`

### Expected Response Format

```json
{
  "enabled": true,
  "announcement": "Optional message to users",
  "logo_url": "https://example.com/logo.png",
  "app_title": "Silent Multi Panel",
  "panels": [
    {
      "name": "Panel Display Name",
      "url": "https://website-to-load.com",
      "site_key": "unique_identifier_for_credentials"
    },
    {
      "name": "Another Panel",
      "url": "https://another-website.com",
      "site_key": "another_id"
    }
  ]
}
```

### Response Fields

| Field | Type | Purpose |
|-------|------|---------|
| enabled | boolean | Set to false to disable app |
| announcement | string | Message shown to users |
| logo_url | string | URL to app logo (future use) |
| app_title | string | Custom app title |
| panels | array | List of panel objects |

### Panel Object Fields

| Field | Type | Purpose |
|-------|------|---------|
| name | string | Button display text |
| url | string | Website URL to load |
| site_key | string | Unique key for credential storage |

## 🔑 Key Classes

### ConfigManager.java

Handles all server communication:
- `fetchConfig(Context, ConfigCallback)` - Fetches configuration
- `parseConfig(String)` - Parses JSON response
- `ConfigCallback` interface - Async callback for results

Usage:
```java
ConfigManager.fetchConfig(context, new ConfigManager.ConfigCallback() {
    @Override
    public void onConfigLoaded(Config config) {
        // Use configuration
    }
    
    @Override
    public void onConfigFailed(String error) {
        // Handle error
    }
});
```

### Config.java

Data model for complete app configuration:
- `isServerEnabled()` - Check if app is enabled
- `getAnnouncement()` - Get user message
- `getPanels()` - Get list of panels
- `getAppTitle()` - Get app title
- `getLogoUrl()` - Get logo URL

### Panel.java

Data model for individual panels:
- `getName()` - Panel button text
- `getUrl()` - Website URL
- `getSiteKey()` - Credential storage key

## 📱 Activities

### WebsiteSelectorActivity (Modified)

**Changes:**
- Removed hardcoded button bindings
- Added server configuration fetching
- Dynamic button creation
- Shows loading progress
- Falls back to default panels if error

**Key Methods:**
- `fetchConfig()` - Loads config from server
- `setupUI(Config)` - Creates buttons from config
- `createButton(Panel)` - Creates individual button
- `loadDefaultPanels()` - Fallback mechanism

### MainActivity (Modified)

**Changes:**
- Added announcement display
- Checks if server is enabled
- Shows alert if disabled

**Key Methods:**
- `fetchAnnouncements()` - Gets announcements from config
- Shows alert dialog if server disabled
- Shows announcement to user

## 🛠️ Technical Details

### HTTP Communication
- Uses `java.net.HttpURLConnection` (no external library)
- 10-second timeout (configurable)
- GET method for config
- JSON parsing with org.json library

### Threading
- Network calls on background thread
- UI updates on main thread
- Callback pattern for async operations

### Error Handling
- Connection errors handled gracefully
- HTTP error codes checked
- JSON parsing errors caught
- Fallback to default panels

## 🧪 Testing

### Test with Mock Server

Before integration, test with test data:

1. Start your backend server
2. Verify /api/config returns JSON
3. Install modified APK
4. Monitor device logs: `adb logcat`

### Test Scenarios

1. **Normal Operation**
   - Verify panels load from server
   - Click buttons, verify websites load
   - Test credential auto-fill

2. **Server Down**
   - Stop backend server
   - Restart app
   - Verify fallback to default panels

3. **No Announcement**
   - Empty announcement field
   - Verify no dialog shown

4. **Active Announcement**
   - Add announcement via dashboard
   - Restart app
   - Verify dialog appears

5. **Server Disabled**
   - Disable server in dashboard
   - Restart app
   - Verify app shows message and closes

## 📊 Debug Information

### Check If Integration Works

Add logs to WebsiteSelectorActivity:
```java
Log.d("ConfigManager", "Config received: " + panels.size() + " panels");
```

### Monitor Logcat

```bash
adb logcat | grep ConfigManager
```

## 🔒 Security Notes

- Credentials still stored locally (device-only)
- No encryption of stored credentials
- Use HTTPS for server communication (production)
- Validate all data from server
- Consider certificate pinning for production

## 📚 Related Documentation

See `../docs/` folder for:
- INTEGRATION_GUIDE.md - Detailed integration steps
- ANDROID_MODIFICATIONS.md - Technical changes
- ANDROID_APP_SUMMARY.md - Code summary
- BACKEND_API_COMPLETE.md - Backend documentation

## 💡 Common Issues

### App Won't Load Panels
- Check ConfigManager URL is correct
- Verify backend server is running
- Check INTERNET permission in manifest
- Monitor logcat for errors

### Buttons Don't Appear
- Verify JSON response format
- Check panels array is not empty
- Monitor buttonsContainer layout

### Announcements Not Showing
- Verify announcement field is not empty
- Check 'active' flag is true in database

### Server Disabled Alert Shows
- Check app_enabled in database
- Verify /api/config returns enabled:false

---

**Ready to integrate? Start with Step 1 above!**
