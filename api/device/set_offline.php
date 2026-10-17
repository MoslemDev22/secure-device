<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/device_auth.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

try {

    $device = authenticateDevice($pdo);

    $deviceId = $device["device_id"];

    if (empty($deviceId)) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Device ID could not be determined."
        ]);

        exit;
    }

    $sql = "
        UPDATE devices
        SET
            status = 'offline'
        WHERE id = :device_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":device_id" => $deviceId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Device marked as offline successfully.",
        "device_id" => (int)$deviceId
    ]);
} catch (Exception $e) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
