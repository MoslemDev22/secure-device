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
     * Get device logs.
     */
    $stmt = $pdo->prepare("
        SELECT
            id,
            event_type,
            message,
            ip_address,
            created_at
        FROM device_logs
        WHERE device_id = :device_id
        ORDER BY created_at DESC
        LIMIT $limit
    ");

    $stmt->execute([
        "device_id" => $device_id
    ]);

    $logs = $stmt->fetchAll();

    $result = [];

    foreach ($logs as $log) {

        $result[] = [
            "id" => (int)$log["id"],
            "event_type" => $log["event_type"],
            "message" => $log["message"],
            "ip_address" => $log["ip_address"],
            "created_at" => $log["created_at"]
        ];
    }

    echo json_encode([
        "success" => true,
        "message" => "Device logs retrieved successfully.",
        "device" => [
            "device_id" => (int)$device["id"],
            "device_name" => $device["device_name"]
        ],
        "count" => count($result),
        "logs" => $result
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to retrieve device logs."
    ]);
}