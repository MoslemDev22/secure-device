<?php

require_once __DIR__ . "/database.php";

function authenticateDevice(PDO $pdo): array
{
    $headers = getallheaders();

    $authorization = $headers["Authorization"] ?? "";

    if (empty($authorization)) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Authorization header is required."
        ]);

        exit;
    }

    if (!preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid authorization format."
        ]);

        exit;
    }

    $token = trim($matches[1]);

    if ($token === "") {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid device token."
        ]);

        exit;
    }

    $token_hash = hash("sha256", $token);

    $stmt = $pdo->prepare("
        SELECT
            dt.id AS token_id,
            dt.device_id,
            d.device_uuid,
            d.device_name,
            d.status
        FROM device_tokens dt
        INNER JOIN devices d
            ON d.id = dt.device_id
        WHERE dt.token_hash = :token_hash
        LIMIT 1
    ");

    $stmt->execute([
        "token_hash" => $token_hash
    ]);

    $device = $stmt->fetch();

    if (!$device) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid device token."
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

    return $device;
}