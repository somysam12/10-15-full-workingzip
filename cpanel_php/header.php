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
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --dark-blue: #0A0E27;
            --bg-light: #f8f9fc;
        }
        
        body { 
            background: var(--bg-light); 
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        .sidebar {
            background: var(--dark-blue);
            min-height: 100vh;
            color: white;
            padding-top: 20px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding-bottom: 20px;
            }
            .main-content {
                padding: 20px;
            }
        }
        
        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-left: 4px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.08);
            margin-bottom: 25px;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #f1f1f1;
            padding: 15px 20px;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }
        
        .btn {
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background: #3e5fcb; transform: scale(1.02); }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #d1d3e2;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
            border-color: var(--primary-color);
        }
        
        /* Modern Toggle */
        .form-switch .form-check-input {
            width: 3.2em;
            height: 1.6em;
            cursor: pointer;
        }
        
        .logo-preview-big {
            max-width: 120px;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9 col-lg-10 main-content">
<?php if (isset($msg)): ?><div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
