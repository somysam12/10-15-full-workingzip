# Silent Panel Backend - Flask API & Admin Dashboard

Complete backend system for managing Silent Panel Android app remotely.

## 🚀 Quick Start

### Prerequisites
- Python 3.11
- PostgreSQL
- Required packages: flask, psycopg2-binary, python-dotenv

### Installation

```bash
# Install dependencies (already done)
pip install flask psycopg2-binary python-dotenv

# Set up environment
cp .env.example .env
# Update .env with your DATABASE_URL
```

### Run the Server

```bash
python3 app.py
```

Server will start on `http://localhost:5000`

## 📊 Features

### API Endpoints
- `GET /api/config` - Fetch configuration for Android app
- `POST /api/panels/add` - Add new panel
- `DELETE /api/panels/delete/<site_key>` - Delete panel
- `POST /api/announcements/add` - Send announcement
- `POST /api/config/title` - Update app title
- `POST /api/config/logo` - Update logo URL
- `POST /api/config/server` - Toggle server on/off
- `GET /` - Admin dashboard

### Admin Dashboard
Access at `http://localhost:5000/`

Tabs:
- **Overview** - Server status and quick stats
- **Manage Panels** - Add/remove gaming panels
- **Announcements** - Send messages to users
- **Settings** - Customize app title and logo

## 🗄️ Database Schema

### Tables

#### panels
```sql
- id (SERIAL PRIMARY KEY)
- name (VARCHAR)
- url (VARCHAR)
- site_key (VARCHAR UNIQUE)
- position (INTEGER)
- enabled (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### announcements
```sql
- id (SERIAL PRIMARY KEY)
- message (TEXT)
- active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### app_config
```sql
- id (SERIAL PRIMARY KEY)
- key (VARCHAR UNIQUE)
- value (TEXT)
- updated_at (TIMESTAMP)
```

## 🔌 API Usage Examples

### Get Configuration
```bash
curl http://localhost:5000/api/config
```

Response:
```json
{
  "enabled": true,
  "announcement": "Welcome!",
  "logo_url": "",
  "app_title": "Silent Multi Panel",
  "panels": [
    {
      "name": "Panel Name",
      "url": "https://panel.example.com",
      "site_key": "panel_key"
    }
  ]
}
```

### Add Panel
```bash
curl -X POST http://localhost:5000/api/panels/add \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Panel",
    "url": "https://mypanel.com",
    "site_key": "mypanel"
  }'
```

### Send Announcement
```bash
curl -X POST http://localhost:5000/api/announcements/add \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Server maintenance at 2 AM",
    "active": true
  }'
```

### Toggle Server
```bash
curl -X POST http://localhost:5000/api/config/server \
  -H "Content-Type: application/json" \
  -d '{"enabled": false}'
```

## 📁 File Structure

```
backend/
├── app.py              # Flask app (600+ lines)
│   ├── API endpoints
│   ├── Database functions
│   ├── Admin dashboard HTML/CSS/JS
│   └── Configuration
├── .env.example        # Environment template
└── README.md          # This file
```

## 🔐 Security Considerations

### Current
✅ PostgreSQL for data persistence
✅ RESTful API design
✅ JSON responses
✅ Environment variables for secrets

### Recommended for Production
⚠️ Add API authentication (API keys, JWT)
⚠️ Use HTTPS/TLS encryption
⚠️ Add rate limiting
⚠️ Validate all inputs
⚠️ Use strong database passwords
⚠️ Add CORS restrictions

## 🛠️ Configuration

### Environment Variables (.env)

```
DATABASE_URL=postgresql://user:password@host:port/dbname
FLASK_ENV=production
SECRET_KEY=your-secret-key
```

### Default Configuration Values

```
app_enabled = true
app_title = "Silent Multi Panel"
logo_url = ""
admin_password = "admin123"
```

## 📊 Dashboard Features

### Overview Tab
- Server status indicator
- Quick statistics (panel count, announcement status)
- Refresh button to update stats

### Manage Panels Tab
- View all active panels
- Add new panel form
- Delete button for each panel
- Real-time panel loading

### Announcements Tab
- Message text input
- Active toggle switch
- Send button
- Auto-deactivates previous announcements

### Settings Tab
- App title input
- Logo URL input
- Save buttons for each setting

## 🔄 How It Works

### Android App Flow
```
1. App starts
2. Sends GET /api/config
3. Receives JSON configuration
4. Checks if server is enabled
5. Shows active announcement
6. Creates UI buttons from panels
7. User clicks button → Website loads
```

### Admin Dashboard Flow
```
1. Open dashboard
2. Make changes (add panel, announcement, etc.)
3. Click save/send button
4. Data saved to PostgreSQL
5. Android app fetches next time
```

## ⚡ Performance

- Lightweight Flask application
- Direct PostgreSQL queries
- Minimal dependencies
- Fast JSON responses
- Static dashboard assets (no external CDN required)

## 📝 Logs

Server logs appear in console. Monitor for:
- Request errors
- Database connection issues
- JSON parsing errors
- API usage

## 🧪 Testing

### Test API with curl
```bash
# Test GET
curl http://localhost:5000/api/config

# Test POST
curl -X POST http://localhost:5000/api/panels/add \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","url":"https://test.com","site_key":"test"}'
```

### Test Dashboard
1. Open http://localhost:5000/
2. Navigate through tabs
3. Try adding a panel
4. Send an announcement
5. Check /api/config to see changes

## 🚀 Deployment

### For Production

1. **Update Environment**
   - Use production DATABASE_URL
   - Set FLASK_ENV=production
   - Use strong SECRET_KEY

2. **Host Options**
   - Heroku (easy, free tier)
   - AWS (scalable)
   - DigitalOcean (affordable)
   - Google Cloud (reliable)

3. **Domain Setup**
   - Point your domain to server
   - Set up HTTPS certificate
   - Update Android app with production URL

4. **Monitoring**
   - Monitor server logs
   - Set up error alerts
   - Monitor database performance

## 📞 Support

See main README.md for overall project structure and documentation links.

---

**Backend is ready to run! Start with `python3 app.py`**
