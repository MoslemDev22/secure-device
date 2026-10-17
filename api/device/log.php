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

if (empty($input["event_type"])) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "event_type is required."
    ]);

    exit;
}

$event_type = trim($input["event_type"]);

$message = isset($input["message"])
    ? trim($input["message"])
    : null;

$ip_address = $_SERVER["REMOTE_ADDR"] ?? null;

try {

    $stmt = $pdo->prepare("
        INSERT INTO device_logs (
            device_id,
            event_type,
            message,
            ip_address
        ) VALUES (
            :device_id,
            :event_type,
            :message,
            :ip_address
        )
    ");

    $stmt->execute([
        "device_id" => $device["device_id"],
        "event_type" => $event_type,
        "message" => $message,
        "ip_address" => $ip_address
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Device log recorded successfully.",
        "device_id" => (int)$device["device_id"]
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to record device log."
    ]);
}