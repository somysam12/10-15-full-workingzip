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
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --dark-blue: #0A0E27;
            --bg-light: #f8f9fc;
        }
        
        body { 
            background: var(--bg-light); 
            font-family: 'Nunito', sans-serif;
            overflow-x: hidden;
        }
        
        .sidebar {
            background: var(--dark-blue);
            min-height: 100vh;
            color: white;
            padding-top: 20px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 15px 20px;
            transition: all 0.3s;
            display: block;
            text-decoration: none;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            padding: 40px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
        }
        
        .btn-primary { background: var(--primary-color); border: none; padding: 10px 20px; border-radius: 10px; }
        
        /* Modern Toggle */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }
        
        /* Custom Date/Time */
        .datetime-input {
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            padding: 10px;
            width: 100%;
        }
        
        .logo-preview-big {
            max-width: 200px;
            border-radius: 10px;
            border: 3px solid #eee;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="text-center mb-4">
                <h4><i class="fas fa-gamepad"></i> Silent Panel</h4>
            </div>
            <nav>
                <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="panels.php" class="nav-link"><i class="fas fa-list"></i> Manage Panels</a>
                <a href="announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i> Announcements</a>
                <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a>
            </nav>
        </div>
        <div class="col-md-9 col-lg-10 main-content">
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
