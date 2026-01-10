<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $config = getAllConfig();
    $ann = getActiveAnnouncement();
    $panels = getAllPanels();

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

    // ---------- CORE RESPONSE ----------
    $response = [
        /* ================= GLOBAL CONTROL ================= */
        "global_control" => [
            "app_status" => $config['app_status'] ?? 'ON',
            "maintenance_message" => $config['maintenance_message'] ?? '',
            "force_logout" => ($config['force_logout_flag'] ?? 'no') === 'yes'
        ],

        /* ================= VERSION CONTROL ================= */
        "version_management" => [
            "latest_version" => $config['latest_version'] ?? '1.0.0',
            "min_required_version" => (int)($config['min_required_version'] ?? 1),
            "update_url" => $config['update_url'] ?? '',
            "update_message" => $config['update_message'] ?? ''
        ],

        /* ================= VIEWPORT CONTROL ================= */
        "viewport" => [
            "layout_preset" => $config['layout_preset'] ?? 'RIGHT_FOCUS',
            "app_scale" => (float)($config['viewport_app_scale'] ?? 1.25),
            "shift_right_dp" => (int)($config['viewport_shift_right_dp'] ?? 120),
            "shift_down_dp" => (int)($config['viewport_shift_down_dp'] ?? 120),
            "black_left_dp" => (int)($config['viewport_black_left_dp'] ?? 50),
            "container_width_percent" => (int)($config['viewport_container_width_percent'] ?? 92),
            "container_height_percent" => (int)($config['viewport_container_height_percent'] ?? 100)
        ],

        /* ================= AUTO CROP ================= */
        "crop" => [
            "auto_detect_banner" => ($config['crop_auto_detect_banner'] ?? 'true') === 'true',
            "min_banner_height_px" => (int)($config['crop_min_banner_height_px'] ?? 5)
        ],

        /* ================= CSS INJECTION ================= */
        "css" => [
            "enable" => ($config['css_enable'] ?? 'true') === 'true',
            "zoom_scale" => (float)($config['css_zoom_scale'] ?? 1.85),
            "hide_selectors" => array_map(
                'trim',
                explode(',', $config['css_hide_selectors'] ?? 'header,.top-banner,.banner,.vmos-header,.vmos-top')
            )
        ],

        /* ================= MODES ================= */
        "modes" => [
            "focus_mode" => ($config['modes_focus_mode'] ?? 'true') === 'true',
            "lock_reveal" => ($config['modes_lock_reveal'] ?? 'true') === 'true'
        ],

        /* ================= BRANDING ================= */
        "branding" => [
            "splash_logo" => $base_url . "/splash_logo.png",
            "app_logo" => $base_url . "/logo.png",
            "bg_color" => $config['bg_color'] ?? '#0A0E27'
        ],

        /* ================= NEW: LOGIN & MAINTENANCE ================= */
        "app_enabled" => ($config['app_status'] ?? 'ON') === 'ON',
        "maintenance" => [
            "enabled" => ($config['maintenance_enabled'] ?? 'false') === 'true',
            "message" => $config['maintenance_message'] ?? 'App under maintenance. Please come back later.'
        ],
        "announcement" => [
            "enabled" => ($config['announcement_enabled'] ?? 'false') === 'true',
            "title" => $config['announcement_title'] ?? '',
            "message" => $config['announcement_message'] ?? '',
            "type" => $config['announcement_type'] ?? 'info'
        ],
        "login" => [
            "required" => ($config['login_required'] ?? 'true') === 'true',
            "logo_url" => !empty($config['login_logo_url']) ? $config['login_logo_url'] : $base_url . "/logo.png"
        ],

        /* ================= PANELS ================= */
        "panels" => []
    ];

    foreach ($panels as $p) {
        $response['panels'][] = [
            "name" => $p['name'],
            "url" => $p['url'],
            "key" => $p['site_key'],
            "package_name" => $p['package_name'] ?? '',
            "version" => $p['version'] ?? '1.0.0'
        ];
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
