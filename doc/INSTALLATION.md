# Installation and Deployment

This guide is a deployment checklist. Confirm exact database fields, endpoint behavior, and configuration paths in the current source before making production changes.

## 1. Back Up First

- Back up the database.
- Back up the current `api/`, `dashboard/`, and `cron/` directories.
- Preserve existing test and production records.
- Do not run destructive SQL commands merely to follow this guide.

## 2. Database Setup

1. Create a MySQL/MariaDB database and a dedicated database user.
2. Grant only the permissions the application needs.
3. Import the project schema or verify the existing tables.
4. Verify the tables `admin_users`, `admin_tokens`, `devices`, `device_tokens`, `device_heartbeats`, `device_metrics`, and `device_logs`.
5. Store credentials outside public source control.
6. Rotate any database password that has been exposed.

## 3. Upload Server Files

Upload the server-side directories to the correct hosting locations:

- `api/`
- `dashboard/`
- `cron/`

If possible, place scheduled scripts outside the public web root. If the hosting layout requires the script under the web root, restrict HTTP access and allow only server-side CLI execution.

Do not upload `.vs/`, `bin/`, `obj/`, local IDE settings, secrets, or unnecessary publish output.

## 4. PHP and HTTPS

- Confirm PHP and PDO MySQL are enabled.
- Configure production database credentials privately.
- Confirm the site and API use HTTPS.
- Do not display database exceptions, stack traces, or secrets to visitors.
- Write detailed errors to protected server logs.
- Check that API responses use appropriate JSON content types and HTTP status codes.

## 5. Scheduled Offline Check

Configure `cron/check_offline.php` in the hosting scheduler. Use the PHP CLI path provided by the hosting provider. Do not expose an unrestricted public URL that allows visitors to run the offline update.

The offline threshold must be consistent with:
- the Agent heartbeat interval,
- the scheduler frequency,
- normal network delays.

Record the final configured values after checking the current PHP source and cPanel cron settings.

## 6. Administrator Dashboard

- Open the dashboard login page over HTTPS.
- Sign in using an existing administrator account.
- Confirm the device list and device detail page load.
- Confirm metrics charts and logs are displayed.
- Verify protected dashboard endpoints reject requests without a valid administrator token.
- Verify logout and token invalidation behavior if implemented.

## 7. Windows Agent

1. Open `SecureDeviceAgent/SecureDeviceAgent/SecureDeviceAgent.csproj` in Visual Studio 2022.
2. Restore NuGet packages.
3. Build the project.
4. Verify the API base URL in `Services/ApiService.cs`.
5. Run the Agent and check its status in the dashboard.
6. Verify heartbeat, metrics, logs, and reconnection behavior.
7. Publish the Release build using `WINDOWS_AGENT_SETUP.md`.

## 8. Post-Deployment Smoke Test

- The Agent appears online after launch.
- Last Seen changes after a heartbeat.
- Metrics reach the dashboard.
- Logs reach the dashboard.
- Normal Agent shutdown marks the device offline.
- Unexpected termination is detected by the scheduled task.
- Internet reconnection works without creating duplicate devices.
- Invalid administrator and device tokens are rejected.
- No credentials are exposed in HTTP responses or logs.

## 9. Rollback

If deployment fails, restore the previous server files and database backup. Preserve existing device and administrator records. Re-test login, API connectivity, and Agent heartbeat after rollback.
