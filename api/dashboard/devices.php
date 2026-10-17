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

    $stmt = $pdo->query("
        SELECT
            id,
            device_uuid,
            device_name,
            hostname,
            os_name,
            os_version,
            architecture,
            ip_address,
            agent_name,
            agent_version,
            status,
            last_seen_at,
            created_at
        FROM devices
        ORDER BY updated_at DESC
    ");

    $devices = $stmt->fetchAll();

    $result = [];

    foreach ($devices as $device) {
        $result[] = [
            "device_id" => (int)$device["id"],
            "device_uuid" => $device["device_uuid"],
            "device_name" => $device["device_name"],
            "hostname" => $device["hostname"],
            "os_name" => $device["os_name"],
            "os_version" => $device["os_version"],
            "architecture" => $device["architecture"],
            "ip_address" => $device["ip_address"],
            "agent_name" => $device["agent_name"],
            "agent_version" => $device["agent_version"],
            "status" => $device["status"],
            "last_seen_at" => $device["last_seen_at"],
            "created_at" => $device["created_at"]
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => "Devices retrieved successfully.",
        "count" => count($result),
        "devices" => $result
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve devices."
    ]);
}