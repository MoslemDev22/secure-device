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

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}

if (empty($input["device_uuid"])) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "device_uuid is required."
    ]);

    exit;
}

$device_uuid = trim($input["device_uuid"]);

try {

    $stmt = $pdo->prepare("
        SELECT id, status
        FROM devices
        WHERE device_uuid = :device_uuid
        LIMIT 1
    ");

    $stmt->execute([
        "device_uuid" => $device_uuid
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

    if ($device["status"] === "disabled") {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "Device is disabled."
        ]);

        exit;
    }

    /*
     * Generate a secure random token.
     */
    $token = bin2hex(random_bytes(32));

    /*
     * Store only the hash of the token.
     */
    $token_hash = hash("sha256", $token);

    $stmt = $pdo->prepare("
        INSERT INTO device_tokens (
            device_id,
            token_hash
        ) VALUES (
            :device_id,
            :token_hash
        )
    ");

    $stmt->execute([
        "device_id" => $device["id"],
        "token_hash" => $token_hash
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Device token created successfully.",
        "device_id" => (int)$device["id"],
        "device_token" => $token
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to create device token."
    ]);
}