<?php

require_once __DIR__ . "/../config/admin_auth.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET requests are allowed."
    ]);

    exit;
}

$admin = authenticateAdmin($pdo);

try {

    /*
     * Get device statistics.
     */
    $stmt = $pdo->query("
        SELECT
            COUNT(*) AS total_devices,
            SUM(status = 'online') AS online_devices,
            SUM(status = 'offline') AS offline_devices,
            SUM(status = 'disabled') AS disabled_devices
        FROM devices
    ");

    $device_stats = $stmt->fetch();

    /*
     * Get active admin count.
     */
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total_admins
        FROM admin_users
        WHERE is_active = 1
    ");

    $admin_stats = $stmt->fetch();

    /*
     * Get latest device activity.
     */
    $stmt = $pdo->query("
        SELECT
            id,
            device_name,
            status,
            last_seen_at
        FROM devices
        ORDER BY updated_at DESC
        LIMIT 1
    ");

    $latest_activity = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Dashboard statistics retrieved successfully.",
        "stats" => [
            "total_devices" => (int)$device_stats["total_devices"],
            "online_devices" => (int)$device_stats["online_devices"],
            "offline_devices" => (int)$device_stats["offline_devices"],
            "disabled_devices" => (int)$device_stats["disabled_devices"],
            "total_admins" => (int)$admin_stats["total_admins"]
        ],
        "latest_activity" => $latest_activity
            ? [
                "device_id" => (int)$latest_activity["id"],
                "device_name" => $latest_activity["device_name"],
                "status" => $latest_activity["status"],
                "last_seen_at" => $latest_activity["last_seen_at"]
            ]
            : null
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve dashboard statistics."
    ]);
}