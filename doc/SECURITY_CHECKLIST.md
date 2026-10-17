# Security Checklist

Use this checklist before public release or production deployment. Do not check an item until it has been verified in the current code and environment.

## Secrets and Configuration

- [ ] Remove database credentials and secrets from tracked source files.
- [ ] Rotate any database password that has been exposed.
- [ ] Remove secrets from repository history where necessary.
- [ ] Store production configuration outside public source control.
- [ ] Confirm errors and logs never expose passwords, raw tokens, or connection strings.

## Administrator Authentication

- [ ] Passwords are stored using `password_hash` and checked with `password_verify`.
- [ ] Administrator login validates input and uses prepared statements.
- [ ] Protected admin endpoints validate bearer tokens.
- [ ] Administrator creation requires an authenticated, authorized administrator.
- [ ] Tokens have expiry and a defined revocation/logout policy.
- [ ] Login and admin-creation endpoints have suitable abuse protections.

## Device Authentication

- [ ] Protected device endpoints validate device tokens.
- [ ] Token hashes are stored rather than raw reusable tokens.
- [ ] Device token issuance cannot be authorized by UUID alone.
- [ ] Initial registration uses a reviewed enrollment mechanism.
- [ ] Token expiry, revocation, renewal, and cleanup are defined.
- [ ] Invalid tokens and malformed requests are rejected safely.

## API and Database

- [ ] All SQL operations use prepared statements where values are supplied.
- [ ] JSON payloads are validated for type, length, and required fields.
- [ ] HTTP methods are checked.
- [ ] CORS and request-origin behavior are intentional.
- [ ] Database user follows least privilege.
- [ ] Detailed exceptions are logged privately, not returned to clients.

## Offline Scheduler

- [ ] The offline-check script cannot be triggered by arbitrary web visitors.
- [ ] The task is configured in the hosting scheduler.
- [ ] Scheduler frequency and offline threshold are documented.
- [ ] The task logs failures without exposing sensitive information.

## Dashboard and Release

- [ ] Dashboard pages require authentication where appropriate.
- [ ] Logout behavior is defined and tested.
- [ ] `.vs/`, `bin/`, `obj/`, local settings, secrets, and build artifacts are excluded from Git.
- [ ] Release output contains no secrets.
- [ ] Published Agent is tested outside Visual Studio.
- [ ] Backup and rollback procedures are available.

## Release Decision

Do not label the project production-ready until high-risk items are resolved and the relevant tests have passed.
