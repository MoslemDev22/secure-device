
<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/admin_auth.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

// Require a valid, active administrator token.
$admin = authenticateAdmin($pdo);

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}

$username = trim($input["username"] ?? "");
$password = $input["password"] ?? "";
$full_name = trim($input["full_name"] ?? "");

if (
    $username === "" ||
    !is_string($password) ||
    $password === "" ||
    $full_name === ""
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "username, password and full_name are required."
    ]);

    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters."
    ]);

    exit;
}

try {
    $check = $pdo->prepare("
        SELECT id
        FROM admin_users
        WHERE username = :username
        LIMIT 1
    ");

    $check->execute([
        "username" => $username
    ]);

    if ($check->fetch()) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "Username already exists."
        ]);

        exit;
    }

    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        INSERT INTO admin_users (
            username,
            password_hash,
            full_name
        ) VALUES (
            :username,
            :password_hash,
            :full_name
        )
    ");

    $stmt->execute([
        "username" => $username,
        "password_hash" => $password_hash,
        "full_name" => $full_name
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Admin created successfully.",
        "admin_id" => (int)$pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Admin creation failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to create admin."
    ]);
}
