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

if (!isset($_GET["device_id"]) || !is_numeric($_GET["device_id"])) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "device_id is required."
    ]);

    exit;
}

$device_id = (int)$_GET["device_id"];

$limit = 20;

if (isset($_GET["limit"]) && is_numeric($_GET["limit"])) {
    $limit = (int)$_GET["limit"];

    if ($limit < 1) {
        $limit = 1;
    }

    if ($limit > 100) {
        $limit = 100;
    }
}

try {

    /*
     * Check whether device exists.
     */
    $stmt = $pdo->prepare("
        SELECT id, device_name
        FROM devices
        WHERE id = :device_id
        LIMIT 1
    ");

    $stmt->execute([
        "device_id" => $device_id
    ]);

    $device = $stmt->fetch();

    if (!$device) {
        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Device not found."
        ]);

        exit;
    }

    /*
     * Get device metrics.
     */
    $stmt = $pdo->prepare("
        SELECT
            id,
            cpu_usage,
            ram_usage_percent,
            ram_used_mb,
            ram_total_mb,
            disk_usage_percent,
            disk_used_gb,
            disk_free_gb,
            disk_total_gb,
            uptime_seconds,
            recorded_at
        FROM device_metrics
        WHERE device_id = :device_id
        ORDER BY recorded_at DESC
        LIMIT $limit
    ");

    $stmt->execute([
        "device_id" => $device_id
    ]);

    $metrics = $stmt->fetchAll();

    $result = [];

    foreach ($metrics as $metric) {

        $result[] = [
            "id" => (int)$metric["id"],
            "cpu_usage" => $metric["cpu_usage"] !== null
                ? (float)$metric["cpu_usage"]
                : null,
            "ram_usage_percent" => $metric["ram_usage_percent"] !== null
                ? (float)$metric["ram_usage_percent"]
                : null,
            "ram_used_mb" => $metric["ram_used_mb"] !== null
                ? (int)$metric["ram_used_mb"]
                : null,
            "ram_total_mb" => $metric["ram_total_mb"] !== null
                ? (int)$metric["ram_total_mb"]
                : null,
            "disk_usage_percent" => $metric["disk_usage_percent"] !== null
                ? (float)$metric["disk_usage_percent"]
                : null,
            "disk_used_gb" => $metric["disk_used_gb"] !== null
                ? (float)$metric["disk_used_gb"]
                : null,
            "disk_free_gb" => $metric["disk_free_gb"] !== null
                ? (float)$metric["disk_free_gb"]
                : null,
            "disk_total_gb" => $metric["disk_total_gb"] !== null
                ? (float)$metric["disk_total_gb"]
                : null,
            "uptime_seconds" => $metric["uptime_seconds"] !== null
                ? (int)$metric["uptime_seconds"]
                : null,
            "recorded_at" => $metric["recorded_at"]
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => "Device metrics retrieved successfully.",
        "device" => [
            "device_id" => (int)$device["id"],
            "device_name" => $device["device_name"]
        ],
        "count" => count($result),
        "metrics" => $result
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve device metrics."
    ]);
}