<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $config = getAllConfig();
    
    // Structure the response as requested
    $response = [
        "status" => "ok",
        "layout_preset" => $config['layout_preset'] ?? 'RIGHT_FOCUS',
        "viewport" => [
            "app_scale" => (float)($config['viewport_app_scale'] ?? 1.32),
            "shift_right_dp" => (int)($config['viewport_shift_right_dp'] ?? 140),
            "shift_down_dp" => (int)($config['viewport_shift_down_dp'] ?? 0),
            "container_width_percent" => (int)($config['viewport_container_width_percent'] ?? 92),
            "container_height_percent" => (int)($config['viewport_container_height_percent'] ?? 100),
            "black_left_dp" => (int)($config['viewport_black_left_dp'] ?? 40)
        ],
        "crop" => [
            "auto_detect_banner" => ($config['crop_auto_detect_banner'] ?? 'true') === 'true',
            "min_banner_height_px" => (int)($config['crop_min_banner_height_px'] ?? 50)
        ],
        "css" => [
            "enable" => ($config['css_enable'] ?? 'true') === 'true',
            "zoom_scale" => (float)($config['css_zoom_scale'] ?? 1.15),
            "hide_selectors" => explode(',', $config['css_hide_selectors'] ?? 'header,.top-banner,.banner,.vmos-header,.vmos-top')
        ],
        "modes" => [
            "focus_mode" => ($config['modes_focus_mode'] ?? 'true') === 'true',
            "lock_reveal" => ($config['modes_lock_reveal'] ?? 'true') === 'true'
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>