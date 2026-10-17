<?php

require_once __DIR__ . "/database.php";

function authenticateAdmin(PDO $pdo): array
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
            "message" => "Invalid admin token."
        ]);

        exit;
    }

    $token_hash = hash("sha256", $token);

    $stmt = $pdo->prepare("
        SELECT
            at.id AS token_id,
            at.admin_id,
            at.expires_at,
            au.username,
            au.full_name,
            au.is_active
        FROM admin_tokens at
        INNER JOIN admin_users au
            ON au.id = at.admin_id
        WHERE at.token_hash = :token_hash
        LIMIT 1
    ");

    $stmt->execute([
        "token_hash" => $token_hash
    ]);

    $admin = $stmt->fetch();

    if (!$admin) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid admin token."
        ]);

        exit;
    }

    if ((int)$admin["is_active"] !== 1) {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "Admin account is disabled."
        ]);

        exit;
    }

    if (
        !empty($admin["expires_at"]) &&
        strtotime($admin["expires_at"]) < time()
    ) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Admin token has expired."
        ]);

        exit;
    }

    return $admin;
}