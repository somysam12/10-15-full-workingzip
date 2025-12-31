<?php
// config.php - Database configuration
$db_host = 'localhost';
$db_name = 'your_database_name';
$db_user = 'your_database_user';
$db_pass = 'your_database_password';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // For now, let's just return a mock response if DB fails, or handle it as you wish
    // die("Connection failed: " . $e->getMessage());
}

// Function to get current config (Mocking for easy deployment if DB not setup)
function getAppConfig($pdo) {
    return [
        "app_enabled" => true,
        "disable_message" => "App is under maintenance",
        "force_logout" => false,
        "announcement" => [
            "text" => "Server maintenance tonight 10PM – 12AM",
            "start" => "2025-01-01 20:00",
            "end" => "2025-01-01 22:00"
        ],
        "panels" => [
            [
                "name" => "Silent Panel",
                "url" => "https://silentpanel.site",
                "key" => "silent"
            ],
            [
                "name" => "Second Panel",
                "url" => "https://secondpanel.site",
                "key" => "second"
            ]
        ]
    ];
}
?>