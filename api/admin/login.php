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

if (
    empty($input["username"]) ||
    empty($input["password"])
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "username and password are required."
    ]);

    exit;
}

$username = trim($input["username"]);
$password = $input["password"];

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            username,
            password_hash,
            full_name,
            is_active
        FROM admin_users
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute([
        "username" => $username
    ]);

    $admin = $stmt->fetch();

    if (!$admin) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
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

    if (!password_verify($password, $admin["password_hash"])) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid username or password."
        ]);

        exit;
    }

    /*
     * Generate a secure random admin token.
     */
    $token = bin2hex(random_bytes(32));

    /*
     * Store only the hash of the token.
     */
    $token_hash = hash("sha256", $token);

    $expires_at = date(
        "Y-m-d H:i:s",
        time() + (24 * 60 * 60)
    );

    $stmt = $pdo->prepare("
        INSERT INTO admin_tokens (
            admin_id,
            token_hash,
            expires_at
        ) VALUES (
            :admin_id,
            :token_hash,
            :expires_at
        )
    ");

    $stmt->execute([
        "admin_id" => $admin["id"],
        "token_hash" => $token_hash,
        "expires_at" => $expires_at
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Admin login successful.",
        "admin_id" => (int)$admin["id"],
        "username" => $admin["username"],
        "full_name" => $admin["full_name"],
        "admin_token" => $token,
        "expires_at" => $expires_at
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Admin login failed."
    ]);
}