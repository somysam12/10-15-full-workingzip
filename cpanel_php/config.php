<?php
// config.php - Modernized with better structure
$db_host = 'localhost';
$db_name = 'your_db';
$db_user = 'your_user';
$db_pass = 'your_pass';

// Simple JSON file storage for this example to make it "easy to deploy" without DB first
$storage_file = __DIR__ . '/data.json';

if (!file_exists($storage_file)) {
    $initial_data = [
        "app_enabled" => true,
        "disable_message" => "App is under maintenance",
        "force_logout" => false,
        "logo_url" => "logo.png",
        "announcement" => [
            "text" => "Welcome to Silent Panel",
            "start" => date('Y-m-d H:i'),
            "end" => date('Y-m-d H:i', strtotime('+1 day'))
        ],
        "panels" => [
            ["name" => "Silent Panel", "url" => "https://silentpanel.site", "key" => "silent"]
        ]
    ];
    file_put_contents($storage_file, json_encode($initial_data));
}

function getData() {
    global $storage_file;
    return json_decode(file_get_contents($storage_file), true);
}

function saveData($data) {
    global $storage_file;
    file_put_contents($storage_file, json_encode($data, JSON_PRETTY_PRINT));
}
?>