<?php

require_once __DIR__ . "/../config/device_auth.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

$device = authenticateDevice($pdo);

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}

$cpu_usage = isset($input["cpu_usage"])
    ? (float)$input["cpu_usage"]
    : null;

$ram_usage_percent = isset($input["ram_usage_percent"])
    ? (float)$input["ram_usage_percent"]
    : null;

$ram_used_mb = isset($input["ram_used_mb"])
    ? (int)$input["ram_used_mb"]
    : null;

$ram_total_mb = isset($input["ram_total_mb"])
    ? (int)$input["ram_total_mb"]
    : null;

$disk_usage_percent = isset($input["disk_usage_percent"])
    ? (float)$input["disk_usage_percent"]
    : null;

$disk_used_gb = isset($input["disk_used_gb"])
    ? (float)$input["disk_used_gb"]
    : null;

$disk_free_gb = isset($input["disk_free_gb"])
    ? (float)$input["disk_free_gb"]
    : null;

$disk_total_gb = isset($input["disk_total_gb"])
    ? (float)$input["disk_total_gb"]
    : null;

$uptime_seconds = isset($input["uptime_seconds"])
    ? (int)$input["uptime_seconds"]
    : null;

try {

    $stmt = $pdo->prepare("
        INSERT INTO device_metrics (
            device_id,
            cpu_usage,
            ram_usage_percent,
            ram_used_mb,
            ram_total_mb,
            disk_usage_percent,
            disk_used_gb,
            disk_free_gb,
            disk_total_gb,
            uptime_seconds
        ) VALUES (
            :device_id,
            :cpu_usage,
            :ram_usage_percent,
            :ram_used_mb,
            :ram_total_mb,
            :disk_usage_percent,
            :disk_used_gb,
            :disk_free_gb,
            :disk_total_gb,
            :uptime_seconds
        )
    ");

    $stmt->execute([
        "device_id" => $device["device_id"],
        "cpu_usage" => $cpu_usage,
        "ram_usage_percent" => $ram_usage_percent,
        "ram_used_mb" => $ram_used_mb,
        "ram_total_mb" => $ram_total_mb,
        "disk_usage_percent" => $disk_usage_percent,
        "disk_used_gb" => $disk_used_gb,
        "disk_free_gb" => $disk_free_gb,
        "disk_total_gb" => $disk_total_gb,
        "uptime_seconds" => $uptime_seconds
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Device metrics recorded successfully.",
        "device_id" => (int)$device["device_id"]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to record device metrics."
    ]);
}