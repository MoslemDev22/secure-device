# Windows Agent: Build and Publish

## Overview

The Windows Agent is a C# WPF desktop application targeting .NET 8. It communicates with the PHP API to register the device and send heartbeat, metrics, and event-log data.

## Prerequisites

- Windows development machine
- Visual Studio 2022 with the .NET desktop development workload
- .NET 8 SDK
- Network access to the API during runtime

## Open the Project

Open:

`SecureDeviceAgent/SecureDeviceAgent/SecureDeviceAgent.csproj`

Restore NuGet packages before building.

## Configuration

The API base URL is currently configured in:

`SecureDeviceAgent/SecureDeviceAgent/Services/ApiService.cs`

Current value:

`https://moslemdev.ir/api/`

Change it only when intentionally targeting another environment. Do not put credentials or enrollment secrets directly into public source files.

## Build in Visual Studio

1. Open the solution or project.
2. Select the `Release` configuration.
3. Select the appropriate Windows target.
4. Run **Build > Rebuild Solution**.
5. Review the Error List and confirm there are no build errors.
6. Launch the built application and verify API connectivity.

## Publish

1. Right-click the WPF project and select **Publish**.
2. Choose a folder publish target.
3. Select `Release`.
4. Choose the intended deployment mode:
   - **Framework-dependent**: the target machine needs the compatible .NET 8 Windows Desktop Runtime.
   - **Self-contained**: the runtime is included for the selected Windows architecture, resulting in a larger output.
5. Publish to a clean output directory.
6. Test the published application, not only the Visual Studio debug run.

The exact publish options depend on the project configuration and selected runtime identifier.

## Runtime Verification

- The application starts without Visual Studio.
- Device UUID persists across restarts.
- The device does not get duplicated on each launch.
- The Agent obtains a token and authenticates requests.
- Heartbeat and Last Seen update.
- Metrics and logs appear in the dashboard.
- Temporary internet loss does not permanently stop synchronization.
- Normal shutdown marks the device offline.
- Unexpected termination is detected by the server-side timeout task.

## Troubleshooting

### Agent cannot connect
- Confirm the API URL and HTTPS certificate.
- Check the server error logs.
- Verify that the hosting service is reachable.
- Confirm that the API is returning JSON and expected HTTP status codes.

### Device appears offline
- Check the Agent process and network.
- Confirm the heartbeat endpoint succeeds.
- Verify the server scheduler is running.
- Compare heartbeat interval, offline threshold, and cron frequency.

### A duplicate device appears
- Check whether the persistent UUID file was deleted or changed.
- Check whether the Agent is running under a different Windows user profile.
- Inspect registration logic before deleting any database records.

### Authentication fails
- Check the token endpoint response and server logs.
- Verify the token has not expired or been revoked.
- Never log or share raw bearer tokens.

## Release Checklist

- [ ] Release build succeeds.
- [ ] Publish succeeds.
- [ ] Published app runs outside Visual Studio.
- [ ] Correct API URL is configured.
- [ ] No secrets are embedded in the release.
- [ ] Heartbeat, metrics, and logs verified.
- [ ] Normal and unexpected shutdown behavior verified.
- [ ] Package tested on a clean Windows environment.
