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

$ip_address = $_SERVER["REMOTE_ADDR"] ?? null;

try {

    $stmt = $pdo->prepare("
        INSERT INTO device_heartbeats (
            device_id,
            ip_address
        ) VALUES (
            :device_id,
            :ip_address
        )
    ");

    $stmt->execute([
        "device_id" => $device["device_id"],
        "ip_address" => $ip_address
    ]);

    $stmt = $pdo->prepare("
        UPDATE devices
        SET
            status = 'online',
            last_seen_at = NOW()
        WHERE id = :device_id
    ");

    $stmt->execute([
        "device_id" => $device["device_id"]
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Heartbeat received successfully.",
        "device_id" => (int)$device["device_id"],
        "status" => "online",
        "last_seen_at" => date("Y-m-d H:i:s")
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to process heartbeat."
    ]);
}