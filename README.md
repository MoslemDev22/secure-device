# Secure Device Management

A web-based device monitoring platform designed to register and monitor devices through a centralized administrator dashboard.

The project consists of a PHP/MySQL backend, a web dashboard, and a Windows desktop agent developed with C# and WPF.

## Features

- Device registration with a persistent device identifier
- Device authentication using tokens
- Heartbeat reporting and online/offline status monitoring
- CPU, RAM, disk, and uptime metrics collection
- Device event logging
- Administrator authentication
- Centralized dashboard for monitoring registered devices
- Device details, metrics charts, and activity logs
- Scheduled detection of devices that stop sending heartbeats
- Windows Agent build and publishing support

## Technology Stack

| Component | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL |
| Dashboard | HTML, CSS, JavaScript, PHP |
| Windows Agent | C#, WPF, .NET 8 |
| Communication | HTTPS and JSON |
| Hosting | cPanel |

## Project Structure

```text
SecureDeviceManagement/
├── api/
│   ├── admin/
│   ├── config/
│   ├── dashboard/
│   └── device/
├── cron/
│   └── check_offline.php
├── dashboard/
├── SecureDeviceAgent/
└── README.md
```

## How It Works

1. The Windows Agent identifies the device using a persistent UUID.
2. The Agent communicates with the backend API over HTTPS.
3. The backend authenticates requests and stores device information in MySQL.
4. The Agent periodically sends heartbeat and system metrics data.
5. The dashboard displays device status, metrics, and activity logs.
6. The server-side scheduled task detects devices whose heartbeats have become outdated.

## Requirements

### Server
- PHP with PDO MySQL support
- MySQL or MariaDB
- HTTPS-enabled hosting
- A scheduled task for offline detection

### Windows Agent
- Windows operating system
- .NET 8 SDK and Visual Studio 2022 for development
- Network access to the configured API server

## Configuration

The Windows Agent currently uses the following API base URL:

`https://moslemdev.ir/api/`

Before deploying the project to another environment, update the API configuration and set the server database credentials securely.

**Never publish database passwords, API secrets, access tokens, or other private configuration values in a public repository.**

## Current Status

The core backend, administrator dashboard, and Windows Agent have been implemented. Integration testing, security verification, and release documentation should be completed before considering the project production-ready.

## Future Improvements

- Complete security hardening and authentication review
- Improve token lifecycle management
- Expand automated integration tests
- Add comprehensive installation and API documentation
- Develop a Linux/Ubuntu Agent

## License

No license has been specified yet. Add a license before distributing or accepting external contributions.
