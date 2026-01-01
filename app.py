from flask import Flask, render_template_string, request, jsonify
import psycopg2
from psycopg2.extras import RealDictCursor
import os
from dotenv import load_dotenv
import json
import logging

load_dotenv()

logging.basicConfig(level=logging.DEBUG)

app = Flask(__name__)
app.secret_key = os.environ.get("SESSION_SECRET")

# Database connection
def get_db():
    return psycopg2.connect(os.environ.get('DATABASE_URL'))

def init_db():
    """Initialize database tables"""
    conn = get_db()
    cur = conn.cursor()
    
    # Create tables
    cur.execute("""
        CREATE TABLE IF NOT EXISTS app_config (
            id SERIAL PRIMARY KEY,
            key VARCHAR(100) UNIQUE NOT NULL,
            value TEXT
        )
    """)
    
    cur.execute("""
        CREATE TABLE IF NOT EXISTS panels (
            id SERIAL PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            url TEXT NOT NULL,
            site_key VARCHAR(100) UNIQUE NOT NULL,
            position INTEGER DEFAULT 0,
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    """)
    
    cur.execute("""
        CREATE TABLE IF NOT EXISTS announcements (
            id SERIAL PRIMARY KEY,
            message TEXT NOT NULL,
            active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    """)
    
    # Add new columns if they don't exist
    columns_to_add = [
        ("announcements", "title", "VARCHAR(200)"),
        ("announcements", "button_text", "VARCHAR(100)"),
        ("announcements", "button_link", "TEXT"),
        ("announcements", "type", "VARCHAR(50) DEFAULT 'info'"),
        ("announcements", "start_time", "TIMESTAMP"),
        ("announcements", "end_time", "TIMESTAMP")
    ]
    
    for table, column, dtype in columns_to_add:
        cur.execute(f"""
            DO $$ 
            BEGIN 
                IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                               WHERE table_name='{table}' AND column_name='{column}') THEN
                    ALTER TABLE {table} ADD COLUMN {column} {dtype};
                END IF;
            END $$;
        """)
    
    # Default configs
    defaults = {
        'app_enabled': 'true',
        'app_title': 'Silent Multi Panel',
        'logo_url': '',
        'maintenance_message': 'System is under maintenance. Please check back later.',
        'force_logout': 'false',
        'latest_version': '1.0.0',
        'min_required_version': '1',
        'update_url': '',
        'update_message': 'A new update is available!',
        'reset_cache': 'false',
        'reset_time': '',
        'theme_mode': 'System',
        'theme_locked': 'false',
        'splash_text': 'Welcome',
        'splash_text_color': '#ffffff',
        'splash_text_position': 'center',
        'bg_color': '#0A0E27',
        'loader_url': ''
    }
    
    for key, value in defaults.items():
        cur.execute("""
            INSERT INTO app_config (key, value) VALUES (%s, %s)
            ON CONFLICT (key) DO NOTHING
        """, (key, value))
    
    conn.commit()
    cur.close()
    conn.close()

# Initialize database on startup
try:
    init_db()
except Exception as e:
    logging.error(f"Database initialization error: {e}")

# ============= API ENDPOINTS =============

@app.route('/api/config', methods=['GET'])
def get_config():
    """Main endpoint for Android app to fetch configuration"""
    try:
        conn = get_db()
        cur = conn.cursor(cursor_factory=RealDictCursor)
        
        # Get all configs
        cur.execute("SELECT key, value FROM app_config")
        configs = {row['key']: row['value'] for row in cur.fetchall()}
        
        # Get active announcement
        cur.execute("""
            SELECT id, title, message, button_text, button_link, type, start_time, end_time 
            FROM announcements 
            WHERE active = true 
            AND (start_time IS NULL OR start_time <= CURRENT_TIMESTAMP)
            AND (end_time IS NULL OR end_time >= CURRENT_TIMESTAMP)
            ORDER BY created_at DESC LIMIT 1
        """)
        ann = cur.fetchone()
        
        # Get all panels
        cur.execute("SELECT name, url, site_key FROM panels WHERE enabled = true ORDER BY position")
        panels = cur.fetchall()
        
        cur.close()
        conn.close()
        
        protocol = request.headers.get('X-Forwarded-Proto', 'http')
        base_url = f"{protocol}://{request.host}"
        
        return jsonify({
            "global_control": {
                "app_status": "ON" if configs.get('app_enabled') == 'true' else "OFF",
                "maintenance_message": configs.get('maintenance_message', ''),
                "force_logout": configs.get('force_logout') == 'true'
            },
            "version_management": {
                "latest_version": configs.get('latest_version', '1.0.0'),
                "min_required_version": int(configs.get('min_required_version', 1)),
                "update_url": configs.get('update_url', ''),
                "update_message": configs.get('update_message', '')
            },
            "remote_reset": {
                "reset_cache": configs.get('reset_cache') == 'true',
                "reset_time": configs.get('reset_time', '')
            },
            "announcement": ann if ann else None,
            "theme": {
                "mode": configs.get('theme_mode', 'System'),
                "locked": configs.get('theme_locked') == 'true'
            },
            "branding": {
                "splash_logo": configs.get('logo_url') or f"{base_url}/static/logo.png",
                "app_logo": configs.get('logo_url') or f"{base_url}/static/logo.png",
                "loader_animation_url": configs.get('loader_url', ''),
                "splash_text": configs.get('splash_text', ''),
                "splash_text_color": configs.get('splash_text_color', '#ffffff'),
                "splash_text_position": configs.get('splash_text_position', 'center'),
                "bg_color": configs.get('bg_color', '#0A0E27')
            },
            "panels": panels
        })
    except Exception as e:
        logging.error(f"API Error: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/api/update_setting', methods=['POST'])
def update_setting():
    try:
        data = request.json
        key = data.get('key')
        value = str(data.get('value'))
        
        conn = get_db()
        cur = conn.cursor()
        cur.execute("UPDATE app_config SET value = %s WHERE key = %s", (value, key))
        conn.commit()
        cur.close()
        conn.close()
        return jsonify({'success': True})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/panels/add', methods=['POST'])
def add_panel():
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        cur.execute("""
            INSERT INTO panels (name, url, site_key, position) 
            VALUES (%s, %s, %s, (SELECT COALESCE(MAX(position), 0) + 1 FROM panels))
        """, (data['name'], data['url'], data['site_key']))
        conn.commit()
        cur.close()
        conn.close()
        return jsonify({'success': True})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/panels/delete/<site_key>', methods=['DELETE'])
def delete_panel(site_key):
    try:
        conn = get_db()
        cur = conn.cursor()
        cur.execute("DELETE FROM panels WHERE site_key = %s", (site_key,))
        conn.commit()
        cur.close()
        conn.close()
        return jsonify({'success': True})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/announcements/add', methods=['POST'])
def add_announcement():
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        cur.execute("UPDATE announcements SET active = false")
        cur.execute("""
            INSERT INTO announcements (title, message, button_text, button_link, type, active) 
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (data.get('title'), data['message'], data.get('button_text'), data.get('button_link'), data.get('type', 'info'), data.get('active', True)))
        conn.commit()
        cur.close()
        conn.close()
        return jsonify({'success': True})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# ============= ADMIN DASHBOARD =============

DASHBOARD_HTML = """
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Panel Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); overflow: hidden; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .tabs { display: flex; border-bottom: 2px solid #ddd; background: #f9f9f9; }
        .tab-btn { flex: 1; padding: 15px; background: none; border: none; cursor: pointer; font-size: 16px; border-bottom: 3px solid transparent; transition: all 0.3s; }
        .tab-btn.active { color: #667eea; border-bottom-color: #667eea; background: white; }
        .content { padding: 30px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .setting-card { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 2px solid #ddd; }
        .setting-card h3 { margin-bottom: 15px; color: #667eea; }
        .setting-card label { display: block; margin-bottom: 5px; font-weight: 500; }
        .setting-card input, .setting-card textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; }
        .toggle { position: relative; width: 60px; height: 30px; background: #ccc; border-radius: 15px; cursor: pointer; }
        .toggle.active { background: #667eea; }
        .toggle::after { content: ''; position: absolute; width: 26px; height: 26px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: 0.3s; }
        .toggle.active::after { left: 32px; }
        .panel-item { background: #f9f9f9; padding: 15px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid #667eea; }
        button { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; display: none; }
        .message.success { background: #d4edda; color: #155724; display: block; }
        .message.error { background: #f8d7da; color: #721c24; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <header><h1>🎮 Silent Panel Admin</h1></header>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('overview', event)">📊 Overview</button>
            <button class="tab-btn" onclick="switchTab('panels', event)">📱 Panels</button>
            <button class="tab-btn" onclick="switchTab('announcements', event)">📢 Announcements</button>
            <button class="tab-btn" onclick="switchTab('settings', event)">⚙️ Settings</button>
        </div>
        <div class="content">
            <div id="overview" class="tab-content active">
                <div class="settings-grid">
                    <div class="setting-card">
                        <h3>🔌 App Status</h3>
                        <p>Status: <strong id="serverStatus">Checking...</strong></p>
                        <div class="toggle" id="serverToggle" onclick="toggleServer()" style="margin-top:10px"></div>
                    </div>
                    <div class="setting-card">
                        <h3>📈 Stats</h3>
                        <p>Total Panels: <strong id="panelCount">0</strong></p>
                        <button class="btn-primary" onclick="refreshStats()" style="margin-top:10px">Refresh</button>
                    </div>
                </div>
            </div>
            <div id="panels" class="tab-content">
                <div class="setting-card" style="margin-bottom:20px">
                    <h3>Add New Panel</h3>
                    <input type="text" id="newPanelName" placeholder="Name">
                    <input type="text" id="newPanelUrl" placeholder="URL">
                    <input type="text" id="newPanelKey" placeholder="Key">
                    <button class="btn-primary" onclick="addPanel()">Add</button>
                </div>
                <div id="panelsList"></div>
            </div>
            <div id="announcements" class="tab-content">
                <div class="setting-card">
                    <h3>Send Announcement</h3>
                    <textarea id="announcementText" placeholder="Message..."></textarea>
                    <button class="btn-primary" onclick="saveAnnouncement()">Send</button>
                </div>
            </div>
            <div id="settings" class="tab-content">
                <div class="settings-grid">
                    <div class="setting-card">
                        <h3>Branding</h3>
                        <label>Splash Text</label><input type="text" id="splashText">
                        <button class="btn-primary" onclick="updateSetting('splash_text', 'splashText')">Save</button>
                        <label style="margin-top:10px">Background Color</label><input type="color" id="bgColor">
                        <button class="btn-primary" onclick="updateSetting('bg_color', 'bgColor')">Save</button>
                    </div>
                    <div class="setting-card">
                        <h3>Version</h3>
                        <label>Latest Version</label><input type="text" id="latestVersion">
                        <button class="btn-primary" onclick="updateSetting('latest_version', 'latestVersion')">Save</button>
                        <label style="margin-top:10px">Update URL</label><input type="text" id="updateUrl">
                        <button class="btn-primary" onclick="updateSetting('update_url', 'updateUrl')">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function switchTab(id, e) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            e.target.classList.add('active');
            if(id==='panels') loadPanels();
            if(id==='settings') loadSettings();
        }
        function showMsg(t, m) {
            const div = document.createElement('div');
            div.className = `message ${t}`;
            div.textContent = m;
            document.querySelector('.content').prepend(div);
            setTimeout(() => div.remove(), 3000);
        }
        async function refreshStats() {
            try {
                const res = await fetch('/api/config');
                const data = await res.json();
                if (data.error) {
                    showMsg('error', data.error);
                    return;
                }
                document.getElementById('panelCount').textContent = data.panels ? data.panels.length : 0;
                document.getElementById('serverStatus').textContent = data.global_control.app_status;
                document.getElementById('serverToggle').classList.toggle('active', data.global_control.app_status === 'ON');
            } catch (e) {
                console.error(e);
            }
        }
        async function toggleServer() {
            const active = document.getElementById('serverToggle').classList.contains('active');
            await fetch('/api/update_setting', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({key: 'app_enabled', value: !active ? 'true' : 'false'})
            });
            refreshStats();
            showMsg('success', 'Status updated');
        }
        async function loadPanels() {
            const res = await fetch('/api/config');
            const data = await res.json();
            const list = document.getElementById('panelsList');
            list.innerHTML = '';
            data.panels.forEach(p => {
                const item = document.createElement('div');
                item.className = 'panel-item';
                item.innerHTML = `<strong>${p.name}</strong><br><small>${p.url}</small><br>
                <button class="btn-danger" onclick="deletePanel('${p.site_key}')" style="margin-top:10px">Delete</button>`;
                list.appendChild(item);
            });
        }
        async function addPanel() {
            const name = document.getElementById('newPanelName').value;
            const url = document.getElementById('newPanelUrl').value;
            const site_key = document.getElementById('newPanelKey').value;
            await fetch('/api/panels/add', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({name, url, site_key})
            });
            loadPanels();
            showMsg('success', 'Panel added');
        }
        async function deletePanel(key) {
            await fetch(`/api/panels/delete/${key}`, {method: 'DELETE'});
            loadPanels();
            showMsg('success', 'Panel deleted');
        }
        async function saveAnnouncement() {
            const message = document.getElementById('announcementText').value;
            await fetch('/api/announcements/add', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message, active: true})
            });
            showMsg('success', 'Announcement sent');
        }
        async function loadSettings() {
            const res = await fetch('/api/config');
            const data = await res.json();
            document.getElementById('splashText').value = data.branding.splash_text;
            document.getElementById('bgColor').value = data.branding.bg_color;
            document.getElementById('latestVersion').value = data.version_management.latest_version;
            document.getElementById('updateUrl').value = data.version_management.update_url;
        }
        async function updateSetting(key, id) {
            const value = document.getElementById(id).value;
            await fetch('/api/update_setting', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({key, value})
            });
            showMsg('success', 'Setting saved');
        }
        refreshStats();
    </script>
</body>
</html>
"""

@app.route('/')
def dashboard():
    return render_template_string(DASHBOARD_HTML)

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000)
