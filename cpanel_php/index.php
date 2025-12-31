<?php
require_once 'config.php';
$config = getAppConfig($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Panel Admin Dashboard</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #1a73e8; }
        .status { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .online { background: #e6f4ea; color: #1e8e3e; }
        .offline { background: #fce8e6; color: #d93025; }
        .panel-list { margin-top: 20px; }
        .panel-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .announcement { background: #fff4e5; border-left: 4px solid #ffa000; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Silent Panel Admin</h1>
        
        <div class="status <?php echo $config['app_enabled'] ? 'online' : 'offline'; ?>">
            Status: <?php echo $config['app_enabled'] ? '✅ Online' : '❌ Offline'; ?>
        </div>

        <?php if ($config['announcement']['text']): ?>
        <div class="announcement">
            <strong>Announcement:</strong><br>
            <?php echo htmlspecialchars($config['announcement']['text']); ?>
        </div>
        <?php endif; ?>

        <h2>Active Panels</h2>
        <div class="panel-list">
            <?php foreach ($config['panels'] as $panel): ?>
                <div class="panel-item">
                    <strong><?php echo htmlspecialchars($panel['name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($panel['url']); ?></small> (<?php echo htmlspecialchars($panel['key']); ?>)
                </div>
            <?php endforeach; ?>
        </div>
        
        <p style="margin-top: 30px; font-size: 0.8em; color: #666;">
            To deploy on cPanel: Upload all files in this folder to your public_html directory.
        </p>
    </div>
</body>
</html>