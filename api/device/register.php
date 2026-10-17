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

$required_fields = [
    "device_uuid",
    "device_name",
    "os_name"
];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Missing required field: " . $field
        ]);

        exit;
    }
}

$device_uuid = trim($input["device_uuid"]);
$device_name = trim($input["device_name"]);
$hostname = isset($input["hostname"]) ? trim($input["hostname"]) : null;

$os_name = trim($input["os_name"]);
$os_version = isset($input["os_version"])
    ? trim($input["os_version"])
    : null;

$architecture = isset($input["architecture"])
    ? trim($input["architecture"])
    : null;

$cpu_name = isset($input["cpu_name"])
    ? trim($input["cpu_name"])
    : null;

$cpu_cores = isset($input["cpu_cores"])
    ? (int)$input["cpu_cores"]
    : null;

$ram_total_mb = isset($input["ram_total_mb"])
    ? (int)$input["ram_total_mb"]
    : null;

$ip_address = isset($input["ip_address"])
    ? trim($input["ip_address"])
    : null;

$agent_name = isset($input["agent_name"])
    ? trim($input["agent_name"])
    : null;

$agent_version = isset($input["agent_version"])
    ? trim($input["agent_version"])
    : null;

try {

    $check = $pdo->prepare(
        "SELECT id FROM devices WHERE device_uuid = :device_uuid LIMIT 1"
    );

    $check->execute([
        "device_uuid" => $device_uuid
    ]);

    if ($check->fetch()) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "Device is already registered."
        ]);

        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO devices (
            device_uuid,
            device_name,
            hostname,
            os_name,
            os_version,
            architecture,
            cpu_name,
            cpu_cores,
            ram_total_mb,
            ip_address,
            agent_name,
            agent_version,
            status
        ) VALUES (
            :device_uuid,
            :device_name,
            :hostname,
            :os_name,
            :os_version,
            :architecture,
            :cpu_name,
            :cpu_cores,
            :ram_total_mb,
            :ip_address,
            :agent_name,
            :agent_version,
            'offline'
        )
    ");

    $stmt->execute([
        "device_uuid" => $device_uuid,
        "device_name" => $device_name,
        "hostname" => $hostname,
        "os_name" => $os_name,
        "os_version" => $os_version,
        "architecture" => $architecture,
        "cpu_name" => $cpu_name,
        "cpu_cores" => $cpu_cores,
        "ram_total_mb" => $ram_total_mb,
        "ip_address" => $ip_address,
        "agent_name" => $agent_name,
        "agent_version" => $agent_version
    ]);

    $device_id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Device registered successfully.",
        "device_id" => (int)$device_id
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to register device."
    ]);
}