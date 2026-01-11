<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'config.php';

try {
    $config = getAllConfig();
    $ann = getActiveAnnouncement();
    $maintenance = getMaintenanceConfig();

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

    echo json_encode([
      "app_enabled" => !$maintenance['enabled'],

      "maintenance" => [
        "enabled" => (bool)$maintenance['enabled'],
        "message" => $maintenance['message'] ?? "System is under maintenance."
      ],

      "announcement" => [
        "enabled" => (bool)($ann['enabled'] ?? false),
        "title" => $ann['title'] ?? "",
        "message" => $ann['message'] ?? "",
        "type" => $ann['type'] ?? "info"
      ],

      "viewport" => [
        "layout_preset" => $config['layout_preset'] ?? 'RIGHT_FOCUS',
        "app_scale" => (float)($config['viewport_app_scale'] ?? 1.25),
        "shift_right_dp" => (int)($config['viewport_shift_right_dp'] ?? 120),
        "shift_down_dp" => (int)($config['viewport_shift_down_dp'] ?? 120),
        "black_left_dp" => (int)($config['viewport_black_left_dp'] ?? 50),
        "container_width_percent" => (int)($config['viewport_container_width_percent'] ?? 92),
        "container_height_percent" => (int)($config['viewport_container_height_percent'] ?? 100)
      ],

      "css" => [
        "enable" => ($config['css_enable'] ?? 'true') === 'true',
        "zoom_scale" => (float)($config['css_zoom_scale'] ?? 1.85),
        "hide_selectors" => array_map(
            'trim',
            explode(',', $config['css_hide_selectors'] ?? 'header,.top-banner,.banner,.vmos-header')
        )
      ],

      "login" => [
        "required" => ($config['login_required'] ?? 'true') === 'true',
        "logo_url" => !empty($config['login_logo_url']) ? $config['login_logo_url'] : $base_url . "/logo.png"
      ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
