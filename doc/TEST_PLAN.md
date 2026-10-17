# Integration Test Plan

## Test Environment

Record before each test run:

- Date and time:
- Server/API version:
- Windows Agent version:
- Windows version:
- Test device UUID (use a non-sensitive test value):
- Network conditions:
- Cron schedule and offline threshold:

Do not include passwords, raw tokens, or other secrets in test evidence.

## A. Administrator and Dashboard Tests

| ID | Test | Expected result | Status |
|---|---|---|---|
| ADM-01 | Sign in with valid administrator credentials | Login succeeds and token is returned | Not recorded |
| ADM-02 | Sign in with an incorrect password | Request is rejected | Not recorded |
| ADM-03 | Request protected endpoint without token | Request is rejected | Not recorded |
| ADM-04 | Request protected endpoint with invalid/expired token | Request is rejected | Not recorded |
| ADM-05 | Open device list and details | Data loads correctly | Not recorded |
| ADM-06 | Open metrics and logs | Correct records are displayed | Not recorded |
| ADM-07 | Attempt administrator creation without authorization | Request is rejected | Not recorded |

## B. Device Registration and Authentication

| ID | Test | Expected result | Status |
|---|---|---|---|
| DEV-01 | Start Agent for an unregistered test device | Registration and token flow succeeds | Not recorded |
| DEV-02 | Restart Agent with same persistent UUID | Existing device is reused | Not recorded |
| DEV-03 | Send request with invalid device token | Request is rejected | Not recorded |
| DEV-04 | Attempt token issuance using UUID only | Must be prevented by the final enrollment design | Open security verification |

## C. Heartbeat and Offline Status

| ID | Test | Expected result | Status |
|---|---|---|---|
| HB-01 | Start Agent | Device appears online | Not recorded |
| HB-02 | Keep Agent running | Last Seen updates | Not recorded |
| HB-03 | Close Agent normally | Device becomes offline | Not recorded |
| HB-04 | Force-terminate Agent | Scheduler marks device offline after configured timeout | Not recorded |
| HB-05 | Disconnect network while Agent is running | Dashboard behavior matches documented timeout policy | Not recorded |
| HB-06 | Restore network | Agent resumes synchronization without duplicate registration | Not recorded |
| HB-07 | Stop scheduler temporarily | Offline detection delay is understood and documented | Not recorded |

## D. Metrics and Logs

| ID | Test | Expected result | Status |
|---|---|---|---|
| MET-01 | Send metrics | Metrics are stored and shown in dashboard | Not recorded |
| MET-02 | Send a log event | Log is stored and shown in dashboard | Not recorded |
| MET-03 | Send malformed JSON | Request is rejected safely | Not recorded |
| MET-04 | Send missing or invalid fields | Request is rejected with a safe error | Not recorded |

## E. Release Tests

- [ ] Release rebuild succeeds.
- [ ] Publish succeeds.
- [ ] Published app starts without Visual Studio.
- [ ] API URL is correct.
- [ ] Heartbeat, metrics, and logs work in the published build.
- [ ] Normal shutdown behavior is verified.
- [ ] Forced-stop behavior is verified.
- [ ] Network interruption and reconnection are verified.
- [ ] No credentials or raw tokens appear in logs.
- [ ] Tests are repeated after security-related changes.

## Test Reporting

Only mark a test as passed after running it and observing the result. Record defects with steps to reproduce, expected result, actual result, and relevant sanitized logs.
