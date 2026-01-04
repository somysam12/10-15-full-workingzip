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
            z-index: 1050;
            box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            border-right: 1px solid rgba(168, 85, 247, 0.1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            display: block;
            position: relative;
            z-index: 1;
        }
        
        @media (max-width: 992px) {
            .sidebar { 
                transform: translateX(calc(-1 * var(--sidebar-width)));
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content { 
                margin-left: 0 !important; 
                width: 100% !important;
                padding: 20px; 
            }
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
            z-index: 1045;
            border-bottom: 1px solid rgba(168, 85, 247, 0.2);
            width: 100%;
        }
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
