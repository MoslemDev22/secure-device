# System Architecture

## Overview

The system has five main components:

1. Windows Agent
2. PHP API
3. MySQL database
4. Administrator dashboard
5. Server-side scheduled task for offline detection

## High-Level Diagram

```text
+-----------------------+       HTTPS / JSON       +----------------------+
| Windows Agent         | -----------------------> | PHP Device API       |
| C# / WPF / .NET 8     | <----------------------- | Register, token,      |
| Collects system data  |                          | heartbeat, metrics,   |
+-----------------------+                          | logs                  |
                                                   +----------+-----------+
                                                              |
                                                              | PDO
                                                              v
                                                   +----------------------+
                                                   | MySQL                |
                                                   | devices, tokens,     |
                                                   | heartbeats, metrics, |
                                                   | logs, admin users    |
                                                   +----------+-----------+
                                                              ^
                                                              | PDO
+-----------------------+       HTTPS / JSON       +----------+-----------+
| Administrator Browser | <----------------------> | Dashboard and API    |
| Dashboard UI          |                          | Admin authentication |
+-----------------------+                          +----------------------+

Scheduled task: cron/check_offline.php checks stale heartbeats.
```

## Device Lifecycle

1. `DeviceIdentityService` retrieves or creates a persistent UUID on the Windows machine.
2. The Agent requests a device token.
3. If the server indicates that the device is not registered, the Agent attempts registration and retries token acquisition.
4. The Agent periodically sends heartbeats and metrics and may submit event logs.
5. The dashboard retrieves stored device information and displays status.
6. Normal application shutdown attempts to mark the device offline.
7. If the Agent exits unexpectedly, the server-side offline checker should mark the device offline after the configured timeout.

The actual timeout depends on the server code and scheduler configuration; confirm both before deployment.

## Database Entities

The project database includes these logical tables:

- `admin_users`: administrator accounts and password hashes
- `admin_tokens`: hashed administrator tokens and expiry timestamps
- `devices`: registered device identity and status
- `device_tokens`: device token hashes
- `device_heartbeats`: heartbeat records
- `device_metrics`: system metric records
- `device_logs`: Agent event logs

Always use the actual database schema as the source of truth before changing fields or creating migrations. Do not delete existing records as part of documentation or deployment.

## Authentication

- Administrator login validates a password and issues a random bearer token.
- The server stores a hash of the administrator token and its expiry timestamp.
- Protected administrator endpoints should validate the bearer token through the shared authentication helper.
- Device endpoints should validate device tokens through the device authentication helper.
- Device registration and token issuance require special security review. A UUID is an identifier and should not be treated as a secret or as sufficient proof of device ownership.

## Design Boundaries

This project is a device-monitoring platform. It does not currently promise remote command execution, remote desktop access, file management, or software deployment.
