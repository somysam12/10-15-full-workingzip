<?php
// header.php
require_once 'config.php';
$ann = getActiveAnnouncement();
$app_enabled = getConfig('app_enabled', 'true') === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Panel Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #1cc88a;
            --dark-blue: #0A0E27;
            --dark-card: #151936;
            --bg-dark: #070a1f;
            --text-main: #e2e8f0;
            --sidebar-width: 260px;
        }
        
        body { 
            background: var(--bg-dark); 
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-main);
        }
        
        .sidebar {
            background: var(--dark-blue);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.2);
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
        }
        
        @media (max-width: 992px) {
            .sidebar { width: 0; overflow: hidden; }
            .main-content { margin-left: 0; padding: 20px; }
        }
        
        .nav-link {
            color: rgba(255,255,255,0.6) !important;
            padding: 14px 25px !important;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-radius: 0 !important;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .nav-link i { font-size: 1.1rem; width: 25px; margin-right: 15px; }

        .card {
            background: var(--dark-card);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            overflow: hidden;
            color: var(--text-main);
        }
        
        .card-header {
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 20px 25px;
            font-weight: 700;
            color: white;
        }

        .h3, h1, h2, h3, h4, h5, h6 { color: white; }
        .text-gray-800 { color: white !important; }
        .text-muted { color: #a0aec0 !important; }

        .btn { border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }

        .table { color: var(--text-main); }
        .table-bordered { border-color: rgba(255,255,255,0.1); }
        .table-bordered td, .table-bordered th { border-color: rgba(255,255,255,0.1); }
        
        .form-control, .form-select {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 10px;
        }
        .form-control:focus {
            background: rgba(0,0,0,0.3);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .navbar {
            background: var(--dark-blue) !important;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 30px !important;
        }
        .navbar-text { color: rgba(255,255,255,0.8) !important; }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="d-flex">
        <div class="sidebar-container">
            <?php require_once 'sidebar.php'; ?>
        </div>
        <div class="main-content">
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
