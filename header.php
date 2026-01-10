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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            --sidebar-width: 280px;
            --accent-glow: rgba(168, 85, 247, 0.4);
        }
        
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            box-sizing: border-box;
        }

        body { 
            background: var(--bg-dark); 
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-main);
            letter-spacing: -0.011em;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* Sidebar Styling */
        .sidebar {
            background: var(--sidebar-bg);
            height: 100vh;
            color: white;
            position: fixed;
            width: var(--sidebar-width);
            left: 0;
            top: 0;
            z-index: 1050;
            box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            border-right: 1px solid rgba(168, 85, 247, 0.1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 20px;
            /* Scrollbar styling for better visibility */
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) var(--sidebar-bg);
        }
        
        .sidebar nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar nav::-webkit-scrollbar-track {
            background: var(--sidebar-bg);
        }

        .sidebar nav::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        .nav-links {
            display: block;
            width: 100%;
        }

        .sidebar-footer {
            padding: 15px;
            background: var(--sidebar-bg);
            border-top: 1px solid rgba(168, 85, 247, 0.1);
            margin-top: auto;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 2rem;
            min-height: 100vh;
            will-change: margin-left;
        }

        /* Hamburger Button */
        .hamburger-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: var(--primary-color);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
            transition: transform 0.2s ease, background 0.2s ease;
            display: none;
            will-change: transform;
        }

        .hamburger-btn:active {
            transform: scale(0.95);
        }

        /* Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            will-change: opacity;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
                padding: 1rem;
                padding-top: 4.5rem;
            }
            .hamburger-btn {
                display: flex;
            }
        }

        .card {
            background: var(--dark-card) !important;
            border: 1px solid rgba(168, 85, 247, 0.15) !important;
            border-radius: 16px !important;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
            overflow: hidden;
            will-change: transform;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: var(--primary-color) !important;
            box-shadow: 0 12px 24px rgba(168, 85, 247, 0.15) !important;
        }

        .text-gray-800 { color: var(--text-main) !important; font-weight: 700; }
        .text-primary { color: var(--primary-color) !important; }
        .btn-primary { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            border: none !important;
            padding: 12px 28px !important;
            border-radius: 14px !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.4) !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02) !important;
            box-shadow: 0 12px 25px rgba(168, 85, 247, 0.6) !important;
            filter: brightness(1.1);
        }

        .btn-outline-info {
            border: 2px solid var(--primary-color) !important;
            background: rgba(168, 85, 247, 0.1) !important;
            color: white !important;
            border-radius: 14px !important;
            font-weight: 700 !important;
            padding: 12px 24px !important;
            transition: all 0.3s ease !important;
            text-transform: uppercase;
        }

        .btn-outline-info:hover {
            background: var(--primary-color) !important;
            box-shadow: 0 0 20px var(--accent-glow) !important;
            transform: scale(1.02);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
            border: none !important;
            border-radius: 14px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3) !important;
        }

        .btn-danger:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.5) !important;
        }

        .form-check-input {
            width: 3.5em !important;
            height: 1.8em !important;
            background-color: var(--dark-purple) !important;
            border-color: var(--primary-color) !important;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary-color) !important;
            border-color: var(--secondary-color) !important;
            box-shadow: 0 0 15px var(--accent-glow) !important;
        }

        /* Mobile specific fixes */
        @media (max-width: 576px) {
            .main-content { padding: 15px !important; }
            .card { border-radius: 12px !important; }
            h1 { font-size: 1.5rem !important; }
            .btn { width: 100% !important; margin-bottom: 10px; }
            .sidebar { width: 280px !important; }
        }

        .sidebar nav a {
            padding: 14px 24px;
            margin: 4px 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .sidebar nav a i { margin-right: 12px; width: 20px; text-align: center; font-size: 1.1rem; }

        .sidebar nav a:hover, .sidebar nav a.active {
            background: rgba(168, 85, 247, 0.15);
            color: white;
            box-shadow: inset 0 0 10px rgba(168, 85, 247, 0.1);
        }

        .sidebar nav a.active {
            background: linear-gradient(90deg, rgba(168, 85, 247, 0.3), transparent);
            border-left: 4px solid var(--primary-color);
            color: var(--primary-color);
        }
    </style>
</head>
<body>
<button class="hamburger-btn" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container-fluid p-0">
    <div class="d-flex">
        <div class="sidebar-wrapper">
            <?php require_once 'sidebar.php'; ?>
        </div>
        <div class="main-content w-100">
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
