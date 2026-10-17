# API Reference

## Purpose

This document inventories the API endpoints currently identified in the project. It is not a substitute for checking the implementation: request fields, required headers, response bodies, and status codes must be confirmed against each PHP file before this document is treated as a stable external API contract.

## Base URL

`https://moslemdev.ir/api/`

The Windows Agent's API base URL is configured in `SecureDeviceAgent/SecureDeviceAgent/Services/ApiService.cs`.

## Endpoint Inventory

| Endpoint | Expected purpose | Method | Authentication |
|---|---|---|---|
| `admin/login.php` | Authenticate an administrator and issue a token | POST | Credentials in request body |
| `admin/me.php` | Return the authenticated administrator profile | GET | Admin Bearer token |
| `admin/create.php` | Create an administrator account | POST | Must require an authorized admin; verify current code |
| `device/register.php` | Register a device | POST | Enrollment protection must be verified |
| `device/token.php` | Issue a device token | POST | Must not rely on UUID alone; verify current code |
| `device/heartbeat.php` | Receive device heartbeat | POST | Device Bearer token |
| `device/metrics.php` | Receive device metrics | POST | Device Bearer token |
| `device/log.php` | Receive device event log | POST | Device Bearer token |
| `device/set_offline.php` | Mark a device offline on normal shutdown | POST | Device Bearer token |
| `dashboard/stats.php` | Return dashboard summary statistics | GET | Admin Bearer token |
| `dashboard/devices.php` | List registered devices | GET | Admin Bearer token |
| `dashboard/device.php` | Return a device's details | GET | Admin Bearer token |
| `dashboard/metrics.php` | Return device metrics | GET | Admin Bearer token |
| `dashboard/logs.php` | Return device logs | GET | Admin Bearer token |

Paths above are relative to the `api/` directory unless the hosting configuration says otherwise. Confirm the actual folder layout before publishing this table.

## Authentication Headers

Administrator-protected requests are expected to use:

```http
Authorization: Bearer <admin_token>
```

Device-protected requests are expected to use:

```http
Authorization: Bearer <device_token>
```

Never put real tokens in documentation, source control, screenshots, or bug reports.

## Request and Response Contracts

For each endpoint, document the following directly from the current PHP source and Agent call sites:

- HTTP method
- Required headers
- JSON fields and types
- Required versus optional fields
- Success response example with fake values
- Error response examples
- HTTP status codes
- Whether the endpoint changes persistent state

Use fictional tokens and device identifiers in examples. Do not invent field names or response fields without verifying them in the source.

## Security Notes

- Enforce authentication on every protected endpoint.
- Validate JSON content and field types.
- Use prepared statements for database operations.
- Avoid exposing internal exception messages.
- Restrict admin creation.
- Protect initial device enrollment and token issuance.
- Apply expiration and revocation policies to tokens.
- Restrict the offline-check task to server-side execution.
