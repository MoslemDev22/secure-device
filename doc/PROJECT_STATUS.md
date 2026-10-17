# Project Status

## Project Goal

Provide a centralized dashboard to register and monitor devices through a Windows Agent and a PHP/MySQL API.

## Implemented Components

### Backend and Database
- Database connectivity through PHP/PDO
- Device registration and token flow
- Heartbeat endpoint
- Metrics endpoint
- Device log endpoint
- Offline status handling
- Administrator login and token-based authentication
- Dashboard data endpoints

### Dashboard
- Administrator login flow
- Device list and status summary
- Device detail view
- Metrics charts
- Device logs view
- Refresh and navigation controls

### Windows Agent
- WPF desktop application
- Persistent device UUID
- Device/system information collection
- CPU, RAM, and disk metrics collection
- API communication
- Heartbeat and metrics synchronization
- Event log synchronization
- Normal shutdown offline request
- Reconnection behavior observed during development
- Release and publish workflow tested during development

## Previously Reported Test Results

Basic registration, heartbeat, metrics, logs, reconnection, duplicate prevention, and normal-shutdown offline behavior have been reported as working. See `WINDOWS_AGENT_TESTS.md`; re-run tests after final changes.

## Remaining Work

1. Remove and rotate exposed secrets.
2. Secure the cron/offline checker against public invocation.
3. Review initial device enrollment and token issuance.
4. Finalize token expiry, revocation, and cleanup.
5. Verify authentication and input validation across all endpoints.
6. Run the complete integration test plan against the final published Agent.
7. Create a clean `.gitignore` and remove build output from source control.
8. Finalize installation instructions and API request/response examples.
9. Decide on and add a project license if public distribution is intended.

## Scope Notes

A Linux/Ubuntu Agent is not included in the current Windows Agent completion scope. Remote control features are also outside the current scope.

## Progress Estimate

Historical development estimates placed the server plus Windows Agent around 82% complete. This is an informal planning estimate, not a measured quality or security score. The project should be considered functionally developed but not yet security-cleared for production.
