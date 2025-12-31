<?php
require_once 'config.php';
header('Content-Type: application/json');

$app_enabled = getConfig('app_enabled', 'true') === 'true';
$ann = getActiveAnnouncement();
$panels = getAllPanels();

// Format response to match your exact request
$response = [
    "app_enabled" => $app_enabled,
    "disable_message" => "App is under maintenance",
    "force_logout" => false,
    "announcement" => [
        "text" => $ann ? $ann['message'] : "",
        "start" => $ann ? $ann['start_time'] : "",
        "end" => $ann ? $ann['end_time'] : ""
    ],
    "panels" => []
];

foreach ($panels as $p) {
    $response['panels'][] = [
        "name" => $p['name'],
        "url" => $p['url'],
        "key" => $p['site_key']
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>