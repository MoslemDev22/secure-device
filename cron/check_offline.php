<?php

require_once __DIR__ . "/../api/config/database.php";

header("Content-Type: application/json; charset=utf-8");

$offlineMinutes = 2;

try {

    $sql = "
        UPDATE devices
        SET status = 'offline'
        WHERE status = 'online'
          AND last_seen_at IS NOT NULL
          AND last_seen_at < (NOW() - INTERVAL :offline_minutes MINUTE)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":offline_minutes",
        $offlineMinutes,
        PDO::PARAM_INT
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Offline detection completed successfully.",
        "offline_minutes" => $offlineMinutes,
        "devices_updated" => $stmt->rowCount()
    ]);
} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Offline detection failed."
    ]);
}
