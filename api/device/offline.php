<?php

require_once __DIR__ . "/../config/database.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

/*
 * Number of minutes after which a device is considered offline.
 */
$offline_minutes = 2;

try {

    $stmt = $pdo->prepare("
        UPDATE devices
        SET status = 'offline'
        WHERE status = 'online'
        AND (
            last_seen_at IS NULL
            OR last_seen_at < DATE_SUB(NOW(), INTERVAL :offline_minutes MINUTE)
        )
    ");

    $stmt->execute([
        "offline_minutes" => $offline_minutes
    ]);

    $updated_devices = $stmt->rowCount();

    echo json_encode([
        "success" => true,
        "message" => "Offline detection completed successfully.",
        "offline_minutes" => $offline_minutes,
        "devices_updated" => $updated_devices
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to detect offline devices."
    ]);
}