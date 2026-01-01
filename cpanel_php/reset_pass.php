<?php
require_once 'config.php';

// --- CONFIGURATION ---
$new_username = 'admin';
$new_password = 'admin123'; // CHANGE THIS TO YOUR DESIRED PASSWORD
// ---------------------

try {
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$new_username]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Update existing
        $update = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
        $update->execute([$hash, $admin['id']]);
        echo "Password updated successfully for user: " . htmlspecialchars($new_username);
    } else {
        // Create new
        $insert = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $insert->execute([$new_username, $hash]);
        echo "Admin user created successfully: " . htmlspecialchars($new_username);
    }
    
    echo "<br><br><b>IMPORTANT: DELETE THIS FILE (reset_pass.php) IMMEDIATELY FOR SECURITY!</b>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>