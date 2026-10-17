
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

$input = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($input)) {
    $input = [];
}

// IP address reported by the agent (local device IP).
$reported_ip = isset($input["ip_address"])
    ? trim((string)$input["ip_address"])
    : null;

// Accept only valid IPv4 or IPv6 addresses.
if (
    $reported_ip === null ||
    filter_var($reported_ip, FILTER_VALIDATE_IP) === false
) {
    $reported_ip = null;
}

// IP address observed by the web server.
$remote_ip = $_SERVER["REMOTE_ADDR"] ?? null;

try {
    $pdo->beginTransaction();

    // Preserve the server-observed IP in heartbeat history.
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
        "ip_address" => $remote_ip
    ]);

    // Update the device's reported local IP when valid.
    // If no valid IP is provided, preserve the existing value.
    $stmt = $pdo->prepare("
        UPDATE devices
        SET
            status = 'online',
            last_seen_at = NOW(),
            ip_address = COALESCE(:ip_address, ip_address)
        WHERE id = :device_id
    ");

    $stmt->execute([
        "ip_address" => $reported_ip,
        "device_id" => $device["device_id"]
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Heartbeat received successfully.",
        "device_id" => (int)$device["device_id"],
        "status" => "online",
        "ip_address" => $reported_ip,
        "last_seen_at" => date("Y-m-d H:i:s")
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Heartbeat processing failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to process heartbeat."
    ]);
}
