# Setup & Integration Guide - Complete System

## ✅ Current Status: READY TO USE

Your complete Android + Backend system is fully organized and operational!

## 📂 Where Everything Is

### Backend (Flask Server)
```
/backend/
├── app.py              ← Main Flask application (running now on port 5000)
├── .env.example        ← Environment template
└── README.md           ← Backend documentation
```
**Status**: ✅ Running live at http://localhost:5000/

### Android Code
```
/android/
├── ConfigManager.java           ← [NEW] Server communication
├── Config.java                  ← [NEW] Configuration model
├── Panel.java                   ← [NEW] Panel model
├── WebsiteSelectorActivity.java ← [MODIFIED] Dynamic buttons
├── MainActivity.java            ← [MODIFIED] Announcements
├── build.gradle                 ← [MODIFIED] JSON dependency
└── README.md                    ← Integration instructions
```
**Status**: ✅ Ready to copy to your Android project

### Documentation
```
/docs/
├── QUICKSTART.md                 ← Start here (5 min read)
├── BACKEND_API_COMPLETE.md       ← Full API docs
├── INTEGRATION_GUIDE.md          ← Android integration steps
├── ANDROID_MODIFICATIONS.md      ← Technical changes
└── ANDROID_APP_SUMMARY.md        ← Code summary
```
**Status**: ✅ Complete and detailed

### Main Project Files
```
/
├── README.md              ← Project overview
├── PROJECT_STRUCTURE.md   ← Detailed structure explanation
├── SETUP_GUIDE.md         ← This file
├── replit.md              ← Project metadata
└── .gitignore             ← Git ignore rules
```

---

## 🚀 How to Use Right Now

### Option 1: Test the Backend (2 minutes)
```bash
# Test API endpoint
curl http://localhost:5000/api/config

# Open admin dashboard
# Click preview button in Replit
# You'll see the control panel
```

### Option 2: Add a Panel Immediately
1. Click preview button → Admin dashboard opens
2. Go to "Manage Panels" tab
3. Add:
   - Name: `Test Panel`
   - URL: `https://test.com`
   - Site Key: `test`
4. Click "Add Panel"
5. Check API response: `curl http://localhost:5000/api/config`
6. New panel appears in JSON! ✅

### Option 3: Send an Announcement
1. Dashboard → "Announcements" tab
2. Type message: `Hello Users!`
3. Click "Send"
4. Check API: `curl http://localhost:5000/api/config`
5. Announcement shows in response! ✅

---

## 📱 Integrate with Your Android App (15 minutes)

### Step 1: Copy Files
From `/android/` folder to your Android project:
```
Copy these 6 files to: app/src/main/java/com/silentpanel/app/

ConfigManager.java           → Copy
Config.java                  → Copy
Panel.java                   → Copy
WebsiteSelectorActivity.java → Replace existing
MainActivity.java            → Replace existing
build.gradle                 → Replace existing (app-level)
```

### Step 2: Update Server URL
Open `/android/ConfigManager.java` and change line ~16:

**Before:**
```java
private static final String CONFIG_URL = "https://your-backend-api.com/api/config";
```

**After:**
```java
private static final String CONFIG_URL = "http://localhost:5000/api/config";
// For testing locally

// For production:
// private static final String CONFIG_URL = "https://your-production-server.com/api/config";
```

### Step 3: Add Internet Permission
Edit `AndroidManifest.xml` and add:
```xml
<uses-permission android:name="android.permission.INTERNET" />
```

### Step 4: Build & Test
```bash
# In Android Studio
Build → Build Bundle(s) / APK(s) → Build APK(s)

# Or command line
./gradlew assembleDebug
```

### Step 5: Test Integration
1. Install APK on device/emulator
2. Open app
3. Verify it loads panels from server
4. Click a panel, verify website loads
5. Test auto-login with credentials

---

## 🔧 Backend Configuration

### Current Setup (Testing)
- **Database**: PostgreSQL (Neon-backed)
- **Server**: Flask (port 5000)
- **Status**: All panels pre-loaded (10 gaming panels)
- **Admin Dashboard**: Available at http://localhost:5000/

### Change Configuration
Edit `/backend/.env`:
```
DATABASE_URL=postgresql://user:password@host:port/dbname
FLASK_ENV=production
SECRET_KEY=your-secret-key
```

### Add Custom Panels via Dashboard
1. Open http://localhost:5000/
2. "Manage Panels" → "Add New Panel"
3. Fill in details
4. Click "Add Panel"
5. Done! Users see new panel next time they restart app

---

## 🌐 Deploy to Production

### Step 1: Choose Hosting
- **Heroku**: Easiest (free tier available)
- **AWS**: Most powerful
- **DigitalOcean**: Best value
- **Google Cloud**: Most reliable

### Step 2: Prepare Files
1. Copy `/backend/app.py`
2. Copy `/backend/.env` (update with production values)
3. Have PostgreSQL connection string ready

### Step 3: Deploy
```bash
# On your hosting provider
git clone your-repo
cd backend
pip install -r requirements.txt
python3 app.py
```

### Step 4: Update Android App
Change ConfigManager.java to use production URL:
```java
private static final String CONFIG_URL = "https://your-production-domain.com/api/config";
```

### Step 5: Rebuild APK
```bash
./gradlew assembleRelease
```

---

## 📊 API Reference

### Get Configuration
```bash
curl http://localhost:5000/api/config
```
Returns: enabled, announcement, app_title, logo_url, panels

### Add Panel
```bash
curl -X POST http://localhost:5000/api/panels/add \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Panel Name",
    "url": "https://panel-url.com",
    "site_key": "unique_key"
  }'
```

### Send Announcement
```bash
curl -X POST http://localhost:5000/api/announcements/add \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Your message here",
    "active": true
  }'
```

### Delete Panel
```bash
curl -X DELETE http://localhost:5000/api/panels/delete/panel_site_key
```

### Toggle Server
```bash
curl -X POST http://localhost:5000/api/config/server \
  -H "Content-Type: application/json" \
  -d '{"enabled": false}'
```

---

## 📚 Documentation Map

| Document | Best For | Time |
|----------|----------|------|
| README.md | Project overview | 5 min |
| QUICKSTART.md | Get started NOW | 5 min |
| backend/README.md | Backend details | 10 min |
| android/README.md | Android integration | 15 min |
| BACKEND_API_COMPLETE.md | Full API reference | 20 min |
| PROJECT_STRUCTURE.md | Understanding structure | 10 min |

---

## ✨ Key Features Ready

✅ **Dynamic Panels** - Add/remove without rebuilding APK
✅ **Admin Dashboard** - Beautiful web interface
✅ **REST API** - Standard JSON endpoints
✅ **Announcements** - Send messages to users
✅ **Server Control** - Turn on/off for maintenance
✅ **Auto-login** - Credential management on device
✅ **Fallback Mode** - App works if server is down
✅ **PostgreSQL** - Reliable data storage

---

## 🧪 Testing Checklist

- [ ] API returns config: `curl http://localhost:5000/api/config`
- [ ] Dashboard opens: http://localhost:5000/
- [ ] Can add panel via dashboard
- [ ] Can send announcement
- [ ] Can view panels in API response
- [ ] Android app connects to server
- [ ] New panels appear in Android app
- [ ] Website loads when button clicked
- [ ] Credentials are captured and auto-filled

---

## 🚨 Common Issues & Solutions

### Dashboard Won't Load
- Check backend is running: `curl http://localhost:5000/`
- Check port 5000 is available
- Check logs in Replit console

### API Returns Empty Panels
- Check database is initialized
- Verify SQL ran successfully
- Check if panels were deleted

### Android App Can't Connect
- Verify backend is running
- Check internet permission in AndroidManifest.xml
- Verify ConfigManager.java has correct URL
- Check if device can reach server

### Announcements Not Showing
- Verify announcement is active in dashboard
- Check API includes announcement field
- Clear Android app cache

---

## 📖 Next Steps

### For Testing
1. Open http://localhost:5000/
2. Add test panel via dashboard
3. Send test announcement
4. Verify in API response

### For Android Integration
1. See `/android/README.md`
2. Copy files to your project
3. Update ConfigManager.java URL
4. Rebuild and test

### For Production
1. Choose hosting provider
2. Deploy backend server
3. Update Android app with production URL
4. Rebuild and distribute APK
5. Use dashboard to manage remotely

---

## 📞 Support

**Backend Issues?** → See `backend/README.md`
**Android Issues?** → See `android/README.md`
**API Issues?** → See `docs/BACKEND_API_COMPLETE.md`
**Integration Help?** → See `docs/INTEGRATION_GUIDE.md`
**Quick Start?** → See `docs/QUICKSTART.md`

---

**Everything is organized, documented, and ready to use!**

**Start with:** http://localhost:5000/ (preview button)
