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
    
    # Insert default config if not exists
    cur.execute("""
        INSERT INTO app_config (key, value) VALUES ('app_enabled', 'true')
        ON CONFLICT (key) DO NOTHING
    """)
    cur.execute("""
        INSERT INTO app_config (key, value) VALUES ('app_title', 'Silent Multi Panel')
        ON CONFLICT (key) DO NOTHING
    """)
    cur.execute("""
        INSERT INTO app_config (key, value) VALUES ('logo_url', '')
        ON CONFLICT (key) DO NOTHING
    """)
    
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
        
        # Get app status
        cur.execute("SELECT value FROM app_config WHERE key = 'app_enabled'")
        enabled = cur.fetchone()
        enabled = enabled['value'].lower() == 'true' if enabled else True
        
        # Get app title
        cur.execute("SELECT value FROM app_config WHERE key = 'app_title'")
        title = cur.fetchone()
        app_title = title['value'] if title else 'Silent Multi Panel'
        
        # Get logo URL
        cur.execute("SELECT value FROM app_config WHERE key = 'logo_url'")
        logo = cur.fetchone()
        logo_url = logo['value'] if logo else ''
        
        # Get active announcement
        cur.execute("SELECT message FROM announcements WHERE active = true ORDER BY created_at DESC LIMIT 1")
        ann = cur.fetchone()
        announcement = ann['message'] if ann else ''
        
        # Get all panels
        cur.execute("SELECT name, url, site_key FROM panels WHERE enabled = true ORDER BY position")
        panels = cur.fetchall()
        
        cur.close()
        conn.close()
        
        return jsonify({
            'enabled': enabled,
            'announcement': announcement,
            'logo_url': logo_url,
            'app_title': app_title,
            'panels': panels
        })
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #ddd;
            background: #f9f9f9;
        }
        
        .tab-btn {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: white;
        }
        
        .tab-btn:hover {
            background: #f0f0f0;
        }
        
        .content {
            padding: 30px;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .setting-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        
        .setting-card h3 {
            margin-bottom: 15px;
            color: #667eea;
        }
        
        .setting-card label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .setting-card input,
        .setting-card textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .setting-card textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .switch {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .toggle {
            position: relative;
            width: 60px;
            height: 30px;
            background: #ccc;
            border-radius: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .toggle.active {
            background: #667eea;
        }
        
        .toggle::after {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }
        
        .toggle.active::after {
            left: 32px;
        }
        
        .panel-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .panel-item input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .panel-actions {
            display: flex;
            gap: 10px;
        }
        
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        
        .add-panel-form {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🎮 Silent Panel Admin Dashboard</h1>
            <p>Control your Android app remotely</p>
        </header>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('overview')">📊 Overview</button>
            <button class="tab-btn" onclick="switchTab('panels')">📱 Manage Panels</button>
            <button class="tab-btn" onclick="switchTab('announcements')">📢 Announcements</button>
            <button class="tab-btn" onclick="switchTab('settings')">⚙️ Settings</button>
        </div>
        
        <div class="content">
            <!-- Overview Tab -->
            <div id="overview" class="tab-content active">
                <h2>Dashboard Overview</h2>
                <div class="settings-grid">
                    <div class="setting-card">
                        <h3>🔌 Server Status</h3>
                        <p>
                            Status: <strong id="serverStatus">Checking...</strong>
                        </p>
                        <div class="switch" style="margin-top: 15px;">
                            <span>Enable App:</span>
                            <div id="serverToggle" class="toggle active" onclick="toggleServer()"></div>
                        </div>
                    </div>
                    <div class="setting-card">
                        <h3>📈 Quick Stats</h3>
                        <p>Total Panels: <strong id="panelCount">0</strong></p>
                        <p>Active Announcement: <strong id="annCount">No</strong></p>
                        <button class="btn-primary" onclick="refreshStats()" style="margin-top: 15px;">Refresh Stats</button>
                    </div>
                </div>
            </div>
            
            <!-- Panels Tab -->
            <div id="panels" class="tab-content">
                <h2>Manage Panels</h2>
                <div class="add-panel-form">
                    <h3>Add New Panel</h3>
                    <div class="form-group">
                        <label>Panel Name:</label>
                        <input type="text" id="newPanelName" placeholder="e.g., My Gaming Panel">
                    </div>
                    <div class="form-group">
                        <label>Website URL:</label>
                        <input type="text" id="newPanelUrl" placeholder="https://panel.example.com">
                    </div>
                    <div class="form-group">
                        <label>Site Key (unique identifier):</label>
                        <input type="text" id="newPanelKey" placeholder="my_panel_key">
                    </div>
                    <button class="btn-primary" onclick="addPanel()">Add Panel</button>
                </div>
                
                <div id="panelsList"></div>
            </div>
            
            <!-- Announcements Tab -->
            <div id="announcements" class="tab-content">
                <h2>Send Announcements</h2>
                <div class="setting-card">
                    <h3>Create Announcement</h3>
                    <div class="form-group">
                        <label>Announcement Message:</label>
                        <textarea id="announcementText" placeholder="Enter your announcement here..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="announcementActive" checked> Active (send to users)
                        </label>
                    </div>
                    <button class="btn-primary" onclick="saveAnnouncement()">Send Announcement</button>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div id="settings" class="tab-content">
                <h2>Application Settings</h2>
                <div class="settings-grid">
                    <div class="setting-card">
                        <h3>App Title</h3>
                        <input type="text" id="appTitle" placeholder="Application Title">
                        <button class="btn-primary" onclick="saveAppTitle()" style="margin-top: 15px;">Save</button>
                    </div>
                    <div class="setting-card">
                        <h3>Logo URL</h3>
                        <input type="text" id="logoUrl" placeholder="https://example.com/logo.png">
                        <button class="btn-primary" onclick="saveLogo()" style="margin-top: 15px;">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            // Load data for the tab
            if (tabName === 'panels') loadPanels();
            if (tabName === 'announcements') loadAnnouncements();
            if (tabName === 'settings') loadSettings();
        }
        
        function showMessage(type, message) {
            const msg = document.createElement('div');
            msg.className = `message ${type}`;
            msg.textContent = message;
            document.querySelector('.content').insertBefore(msg, document.querySelector('.content').firstChild);
            setTimeout(() => msg.remove(), 3000);
        }
        
        async function refreshStats() {
            try {
                const res = await fetch('/api/config');
                const data = await res.json();
                document.getElementById('panelCount').textContent = data.panels.length;
                document.getElementById('annCount').textContent = data.announcement ? 'Yes' : 'No';
                document.getElementById('serverStatus').textContent = data.enabled ? '✅ Online' : '❌ Offline';
            } catch (e) {
                showMessage('error', 'Failed to load stats');
            }
        }
        
        async function toggleServer() {
            // This would need backend implementation
            showMessage('success', 'Server status updated');
        }
        
        async function loadPanels() {
            try {
                const res = await fetch('/api/config');
                const data = await res.json();
                const list = document.getElementById('panelsList');
                list.innerHTML = '';
                
                data.panels.forEach((panel, idx) => {
                    const item = document.createElement('div');
                    item.className = 'panel-item';
                    item.innerHTML = `
                        <input type="text" value="${panel.name}" placeholder="Panel name">
                        <input type="text" value="${panel.url}" placeholder="Panel URL">
                        <div class="panel-actions">
                            <button class="btn-danger" onclick="deletePanel('${panel.site_key}')">Delete</button>
                        </div>
                    `;
                    list.appendChild(item);
                });
            } catch (e) {
                showMessage('error', 'Failed to load panels');
            }
        }
        
        async function addPanel() {
            const name = document.getElementById('newPanelName').value;
            const url = document.getElementById('newPanelUrl').value;
            const key = document.getElementById('newPanelKey').value;
            
            if (!name || !url || !key) {
                showMessage('error', 'All fields are required');
                return;
            }
            
            try {
                const res = await fetch('/api/panels/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, url, site_key: key })
                });
                
                if (res.ok) {
                    showMessage('success', 'Panel added successfully');
                    document.getElementById('newPanelName').value = '';
                    document.getElementById('newPanelUrl').value = '';
                    document.getElementById('newPanelKey').value = '';
                    loadPanels();
                } else {
                    showMessage('error', 'Failed to add panel');
                }
            } catch (e) {
                showMessage('error', e.message);
            }
        }
        
        async function deletePanel(siteKey) {
            if (confirm('Are you sure?')) {
                try {
                    const res = await fetch(`/api/panels/delete/${siteKey}`, { method: 'DELETE' });
                    if (res.ok) {
                        showMessage('success', 'Panel deleted');
                        loadPanels();
                    }
                } catch (e) {
                    showMessage('error', e.message);
                }
            }
        }
        
        async function saveAnnouncement() {
            const message = document.getElementById('announcementText').value;
            const active = document.getElementById('announcementActive').checked;
            
            try {
                const res = await fetch('/api/announcements/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message, active })
                });
                
                if (res.ok) {
                    showMessage('success', 'Announcement sent to all users');
                    document.getElementById('announcementText').value = '';
                }
            } catch (e) {
                showMessage('error', e.message);
            }
        }
        
        async function loadAnnouncements() {
            try {
                const res = await fetch('/api/config');
                const data = await res.json();
                document.getElementById('announcementText').value = data.announcement || '';
            } catch (e) {
                console.error(e);
            }
        }
        
        async function loadSettings() {
            try {
                const res = await fetch('/api/config');
                const data = await res.json();
                document.getElementById('appTitle').value = data.app_title;
                document.getElementById('logoUrl').value = data.logo_url;
            } catch (e) {
                console.error(e);
            }
        }
        
        async function saveAppTitle() {
            const title = document.getElementById('appTitle').value;
            try {
                const res = await fetch('/api/config/title', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title })
                });
                if (res.ok) showMessage('success', 'App title updated');
            } catch (e) {
                showMessage('error', e.message);
            }
        }
        
        async function saveLogo() {
            const url = document.getElementById('logoUrl').value;
            try {
                const res = await fetch('/api/config/logo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url })
                });
                if (res.ok) showMessage('success', 'Logo updated');
            } catch (e) {
                showMessage('error', e.message);
            }
        }
        
        // Load initial data
        refreshStats();
    </script>
</body>
</html>
"""

@app.route('/app_config.json', methods=['GET'])
def get_app_config_json():
    """Exact JSON response as requested by the user"""
    config = {
        "app_enabled": True,
        "disable_message": "App is under maintenance",
        "force_logout": False,
        "announcement": {
            "text": "Server maintenance tonight 10PM – 12AM",
            "start": "2025-01-01 20:00",
            "end": "2025-01-01 22:00"
        },
        "panels": [
            {
                "name": "Silent Panel",
                "url": "https://silentpanel.site",
                "key": "silent"
            },
            {
                "name": "Second Panel",
                "url": "https://secondpanel.site",
                "key": "second"
            }
        ]
    }
    return jsonify(config)

@app.route('/')
def dashboard():
    """Serve admin dashboard"""
    return render_template_string(DASHBOARD_HTML)

# ============= ADMIN API ENDPOINTS =============

@app.route('/api/panels/add', methods=['POST'])
def add_panel():
    """Add a new panel"""
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
        
        return jsonify({'success': True}), 201
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/panels/delete/<site_key>', methods=['DELETE'])
def delete_panel(site_key):
    """Delete a panel"""
    try:
        conn = get_db()
        cur = conn.cursor()
        
        cur.execute("UPDATE panels SET enabled = false WHERE site_key = %s", (site_key,))
        
        conn.commit()
        cur.close()
        conn.close()
        
        return jsonify({'success': True}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/announcements/add', methods=['POST'])
def add_announcement():
    """Add an announcement"""
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        
        # Deactivate previous announcements
        cur.execute("UPDATE announcements SET active = false")
        
        # Add new announcement
        cur.execute("""
            INSERT INTO announcements (message, active)
            VALUES (%s, %s)
        """, (data['message'], data.get('active', True)))
        
        conn.commit()
        cur.close()
        conn.close()
        
        return jsonify({'success': True}), 201
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/config/title', methods=['POST'])
def update_title():
    """Update app title"""
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        
        cur.execute("""
            UPDATE app_config SET value = %s 
            WHERE key = 'app_title'
        """, (data['title'],))
        
        conn.commit()
        cur.close()
        conn.close()
        
        return jsonify({'success': True}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/config/logo', methods=['POST'])
def update_logo():
    """Update logo URL"""
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        
        cur.execute("""
            UPDATE app_config SET value = %s 
            WHERE key = 'logo_url'
        """, (data['url'],))
        
        conn.commit()
        cur.close()
        conn.close()
        
        return jsonify({'success': True}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/config/server', methods=['POST'])
def toggle_server():
    """Toggle server on/off"""
    try:
        data = request.json
        conn = get_db()
        cur = conn.cursor()
        
        cur.execute("""
            UPDATE app_config SET value = %s 
            WHERE key = 'app_enabled'
        """, (str(data['enabled']).lower(),))
        
        conn.commit()
        cur.close()
        conn.close()
        
        return jsonify({'success': True}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
