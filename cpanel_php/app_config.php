<?php
require_once 'config.php';
header('Content-Type: application/json');

$config = getAppConfig($pdo);
echo json_encode($config, JSON_PRETTY_PRINT);
?>