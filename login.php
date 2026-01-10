<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $username;
        
        $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?")->execute([$admin['id']]);
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Silent Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: radial-gradient(circle at top right, #1a1f3c, #0A0E27); 
            color: white; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Inter', sans-serif;
        }
        .login-card { 
            background: rgba(26, 31, 60, 0.8); 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 20px; 
            padding: 40px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        }
        .form-label { font-weight: 600; color: #a0aec0; margin-bottom: 8px; }
        .form-control { 
            background: rgba(10, 14, 39, 0.5); 
            border: 1px solid #2e365f; 
            color: white; 
            padding: 12px;
            border-radius: 10px;
        }
        .form-control:focus { 
            background: rgba(10, 14, 39, 0.8); 
            color: white; 
            border-color: #667eea; 
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); 
        }
        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            border: none; 
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            margin-top: 10px;
            transition: transform 0.2s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        h3 { font-weight: 800; letter-spacing: -0.5px; margin-bottom: 30px !important; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">Silent Panel Admin</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>
