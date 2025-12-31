<?php
require_once 'config.php';
$data = getData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $data['app_enabled'] = isset($_POST['app_enabled']);
        $data['announcement']['text'] = $_POST['ann_text'];
        $data['announcement']['start'] = $_POST['ann_start'];
        $data['announcement']['end'] = $_POST['ann_end'];
        saveData($data);
        $msg = "Settings updated!";
    }
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'png') {
            move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/logo.png');
            $data['logo_url'] = 'logo.png';
            saveData($data);
            $msg = "Logo uploaded successfully!";
        } else {
            $error = "Only PNG files allowed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fe; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-primary { background: #4e73df; border: none; padding: 10px 25px; border-radius: 10px; }
        .sidebar { background: #4e73df; color: white; min-height: 100vh; padding: 20px; }
        .logo-preview { max-width: 100px; height: auto; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar d-none d-md-block">
            <h3>Silent Panel</h3>
            <hr>
            <p>Admin Dashboard</p>
        </div>
        <div class="col-md-10 p-5">
            <h2 class="mb-4">Dashboard Settings</h2>
            
            <?php if (isset($msg)): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="card p-4 mb-4">
                        <h5>General Settings</h5>
                        <form method="POST">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="app_enabled" <?php echo $data['app_enabled'] ? 'checked' : ''; ?>>
                                <label class="form-check-label">App Active Status</label>
                            </div>
                            
                            <h5 class="mt-4">Announcement Duration</h5>
                            <div class="mb-3">
                                <label>Message</label>
                                <textarea name="ann_text" class="form-control"><?php echo htmlspecialchars($data['announcement']['text']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label>Start Time</label>
                                    <input type="datetime-local" name="ann_start" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($data['announcement']['start'])); ?>">
                                </div>
                                <div class="col">
                                    <label>End Time</label>
                                    <input type="datetime-local" name="ann_end" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($data['announcement']['end'])); ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_settings" class="btn btn-primary mt-4">Save All Settings</button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="card p-4">
                        <h5>App Logo (logo.png)</h5>
                        <div class="text-center p-3">
                            <img src="logo.png?v=<?php echo time(); ?>" class="logo-preview border" alt="Logo">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <input type="file" name="logo" class="form-control" accept=".png">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Upload New Logo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>