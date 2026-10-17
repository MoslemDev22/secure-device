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

echo json_encode([
    "success" => true,
    "message" => "Admin authentication successful.",
    "admin" => [
        "id" => (int)$admin["admin_id"],
        "username" => $admin["username"],
        "full_name" => $admin["full_name"],
        "is_active" => (int)$admin["is_active"]
    ]
]);