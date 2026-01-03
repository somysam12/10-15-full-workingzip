<?php
// header.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
$ann = getActiveAnnouncement();
$app_status = getConfig('app_status', 'ON');
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
            .sidebar { 
                transform: translateX(calc(-1 * var(--sidebar-width)));
                transition: transform 0.3s ease;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                visibility: visible !important;
                width: var(--sidebar-width) !important;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content { margin-left: 0 !important; padding: 20px; }
            .mobile-header { display: flex !important; }
        }
        
        .mobile-header {
            display: none;
            background: var(--dark-blue);
            padding: 15px 20px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1100;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar { overflow-y: auto; }
    </style>
</head>
<body>
<div class="mobile-header">
    <button class="btn text-white p-0" id="mobile-toggle">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    <h5 class="mb-0 fw-bold">Silent Panel</h5>
    <div style="width: 24px;"></div>
</div>
<div class="container-fluid p-0">
    <div class="d-flex">
        <div class="sidebar-container">
            <?php require_once 'sidebar.php'; ?>
        </div>
        <div class="main-content w-100">
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
