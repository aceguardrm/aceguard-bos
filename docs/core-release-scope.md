# BOS core release scope

## Available functionality
- Executive dashboard and ranked actions.
- Organisation workspaces and existing Business Pulse/security assessments.
- Projects and project tasks.
- Authenticator MFA and recovery codes for enrolled accounts.
- Workspace viewer, editor and administrator roles.
- Platform administrator access.
- Member management and email invitations.
- Workspace access history.
- Private PDF/TXT documents, up to 10 MiB each.
- Document upload/deletion for editors and administrators.
- Document listing/download for authorised workspace members.
- Knowledge Hub navigation and workspace document counts.

## Planned, outside this core release
Microsoft 365 integration, Compliance, Risk Centre, Planner, Reports,
AI Executive and the Organisation settings module.
These navigation items are labelled Planned and do not act as links.

## Known limits
- Files are not scanned for malware.
- MFA is available; universal enrolment enforcement is not established.
- Access history records access changes, not all application activity.
- Audit model hooks do not prevent direct SQL modification.
- Document filesystem deletion cannot be rolled back by a database transaction.
- Workspace deletion through the application is blocked while documents remain.
- Direct SQL or bulk model deletion bypasses the controller deletion guard.
- SQLite tests do not verify MariaDB row-lock concurrency.
- Automated off-server backups, monitoring and restore drills are not verified.

## Release evidence
Documents release: ec3650f.
Staging suite at that release: 77 tests, 410 assertions, passed.
GitHub BOS validation passed for feature commit 0c7acdd.
Production schema, administrator access and private storage read/write/delete
verification passed.
Live browser document upload/download/deletion acceptance remains pending
until explicitly confirmed by the operator.
