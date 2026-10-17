# Windows Agent Test Report

## Scope

This report summarizes tests previously reported as completed during development. Re-run these tests after material changes and record the date/build before treating the report as final release evidence.

## Previously Reported as Working

- [x] Release build and publish were completed during development.
- [x] Published executable launched outside the normal debug workflow.
- [x] Device identity persisted across application restarts.
- [x] Device registration/token flow worked in the tested environment.
- [x] Heartbeat updated the dashboard's Last Seen value.
- [x] Metrics synchronized to the dashboard.
- [x] Logs synchronized to the dashboard.
- [x] Reconnection after restoring internet access was observed.
- [x] Normal application shutdown marked the device offline.
- [x] Duplicate registration was not observed when restarting with the same UUID.

These checkboxes reflect previously reported results, not a guarantee that every current build or deployment has been re-tested.

## Test Device Notes

The development environment previously used a Windows 10 test device. Test records already exist in the database. Preserve existing records unless there is a specific, reviewed reason to change them.

Do not include raw access tokens, passwords, or private server credentials in this report.

## Remaining Verification

- [ ] Record the exact build version and test date.
- [ ] Repeat the tests using the final published build.
- [ ] Verify forced termination and cron-based offline detection.
- [ ] Verify behavior during a long internet outage.
- [ ] Verify token expiry, revocation, and failed authentication behavior.
- [ ] Verify server-side enrollment protections.
- [ ] Test on a clean Windows environment.
- [ ] Review application and server logs for sensitive data.

## Result

**Status: Partially verified.** Basic behavior has been reported as working, but final release verification and security review remain necessary.
