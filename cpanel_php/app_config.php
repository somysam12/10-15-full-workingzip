<?php
require_once 'config.php';
header('Content-Type: application/json');

$data = getData();

// Add full URL to logo if needed
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$data['logo_url'] = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/logo.png";

echo json_encode($data, JSON_PRETTY_PRINT);
?>