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
            --primary-color: #a855f7;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --dark-purple: #0f0720;
            --dark-card: #1c1039;
            --sidebar-bg: #150b2e;
            --bg-dark: #090415;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --sidebar-width: 260px;
            --accent-glow: rgba(168, 85, 247, 0.4);
        }
        
        body { 
            background: var(--bg-dark); 
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-main);
            letter-spacing: -0.011em;
        }
        
        .sidebar {
            background: var(--sidebar-bg);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1000;
            box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            border-right: 1px solid rgba(168, 85, 247, 0.1);
        }

        .card {
            background: var(--dark-card) !important;
            border: 1px solid rgba(168, 85, 247, 0.2) !important;
            border-radius: 16px !important;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3) !important;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color) !important;
            box-shadow: 0 15px 30px rgba(168, 85, 247, 0.15) !important;
        }

        .text-gray-800 { color: var(--text-main) !important; font-weight: 700; }
        .text-primary { color: var(--primary-color) !important; }
        .btn-primary { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            border: none !important;
            padding: 10px 24px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3) !important;
        }

        .form-control, .form-select {
            background: #150b2e !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            color: white !important;
            border-radius: 10px !important;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px var(--accent-glow) !important;
            border-color: var(--primary-color) !important;
        }

        .table { color: var(--text-main) !important; }
        .table-bordered { border-color: rgba(168, 85, 247, 0.1) !important; }
        
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
            background: var(--sidebar-bg);
            padding: 15px 20px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1100;
            border-bottom: 1px solid rgba(168, 85, 247, 0.2);
        }

        .sidebar { overflow-y: auto; }
        h1, h2, h3, h4, h5, h6 { color: white !important; }
        p, span, label { color: var(--text-muted) !important; }
        .badge { font-weight: 600; padding: 6px 12px; border-radius: 8px; }
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
