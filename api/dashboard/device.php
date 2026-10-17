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

try {

    /*
     * Get device information.
     */
    $stmt = $pdo->prepare("
        SELECT
            id,
            device_uuid,
            device_name,
            hostname,
            os_name,
            os_version,
            architecture,
            cpu_name,
            cpu_cores,
            ram_total_mb,
            ip_address,
            agent_name,
            agent_version,
            status,
            last_seen_at,
            created_at,
            updated_at
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
     * Get latest metrics.
     */
    $stmt = $pdo->prepare("
        SELECT
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
        LIMIT 1
    ");

    $stmt->execute([
        "device_id" => $device_id
    ]);

    $metrics = $stmt->fetch();

    /*
     * Get latest heartbeat.
     */
    $stmt = $pdo->prepare("
        SELECT
            ip_address,
            received_at
        FROM device_heartbeats
        WHERE device_id = :device_id
        ORDER BY received_at DESC
        LIMIT 1
    ");

    $stmt->execute([
        "device_id" => $device_id
    ]);

    $heartbeat = $stmt->fetch();

    echo json_encode([
        "success" => true,
        "message" => "Device details retrieved successfully.",

        "device" => [
            "device_id" => (int)$device["id"],
            "device_uuid" => $device["device_uuid"],
            "device_name" => $device["device_name"],
            "hostname" => $device["hostname"],
            "os_name" => $device["os_name"],
            "os_version" => $device["os_version"],
            "architecture" => $device["architecture"],
            "cpu_name" => $device["cpu_name"],
            "cpu_cores" => $device["cpu_cores"] !== null
                ? (int)$device["cpu_cores"]
                : null,
            "ram_total_mb" => $device["ram_total_mb"] !== null
                ? (int)$device["ram_total_mb"]
                : null,
            "ip_address" => $device["ip_address"],
            "agent_name" => $device["agent_name"],
            "agent_version" => $device["agent_version"],
            "status" => $device["status"],
            "last_seen_at" => $device["last_seen_at"],
            "created_at" => $device["created_at"],
            "updated_at" => $device["updated_at"]
        ],

        "latest_metrics" => $metrics
            ? [
                "cpu_usage" => $metrics["cpu_usage"] !== null
                    ? (float)$metrics["cpu_usage"]
                    : null,
                "ram_usage_percent" => $metrics["ram_usage_percent"] !== null
                    ? (float)$metrics["ram_usage_percent"]
                    : null,
                "ram_used_mb" => $metrics["ram_used_mb"] !== null
                    ? (int)$metrics["ram_used_mb"]
                    : null,
                "ram_total_mb" => $metrics["ram_total_mb"] !== null
                    ? (int)$metrics["ram_total_mb"]
                    : null,
                "disk_usage_percent" => $metrics["disk_usage_percent"] !== null
                    ? (float)$metrics["disk_usage_percent"]
                    : null,
                "disk_used_gb" => $metrics["disk_used_gb"] !== null
                    ? (float)$metrics["disk_used_gb"]
                    : null,
                "disk_free_gb" => $metrics["disk_free_gb"] !== null
                    ? (float)$metrics["disk_free_gb"]
                    : null,
                "disk_total_gb" => $metrics["disk_total_gb"] !== null
                    ? (float)$metrics["disk_total_gb"]
                    : null,
                "uptime_seconds" => $metrics["uptime_seconds"] !== null
                    ? (int)$metrics["uptime_seconds"]
                    : null,
                "recorded_at" => $metrics["recorded_at"]
            ]
            : null,

        "latest_heartbeat" => $heartbeat
            ? [
                "ip_address" => $heartbeat["ip_address"],
                "received_at" => $heartbeat["received_at"]
            ]
            : null
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve device details."
    ]);
}