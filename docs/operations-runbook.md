# BOS operations and recovery

## Locations
Production: /var/www/aceguard-crm
Staging: /home/aceguard/bos-mfa-staging
Production branch: release/v0.2.0
Production URL: https://bos.aceguard.co.uk
Production database: MariaDB, aceguardcrm
Staging database: database/staging.sqlite
Private documents: storage/app/workspace-documents

Keep credentials, APP_KEY, invitation tokens and recovery codes out of Git.
Retain APP_KEY securely: encrypted MFA data depends on it.

## Routine use
Platform administrators manage platform access.
Workspace administrators manage members and workspace details.
Editors manage projects, assessments and documents.
Viewers have read-only workspace access and may download documents.
An invitation is addressed to one email, expires after seven days and is
single use. Existing accounts must sign in with the invited address.
Review access history from the workspace members page.
Delete documents through Knowledge Hub before deleting a workspace.

## Production deployment
1. Verify clean Git status and the approved release commit.
2. Back up the database, .env, storage/app and current commit.
3. Validate backup archives; retain them outside the web root.
4. Enter maintenance mode with php artisan down.
5. Fast-forward to the exact approved commit.
6. Run php artisan optimize:clear and php artisan migrate --force.
7. Rebuild config, event and route caches.
8. Build Blade caches as www-data:
   sudo -u www-data php artisan view:cache
9. Verify administrator access, schema and private storage using www-data.
10. Run php artisan up and perform the live browser acceptance checks.
If deployment fails, inspect the error before bringing the site live.
Do not run the test suite against the production database.

## Permissions and upload limits
PHP-FPM pool user: www-data.
Compiled Blade views must remain writable by www-data.
Private document root is owned by www-data with directory mode 0700.
Never create a public symlink to private document storage.
PHP-FPM upload_max_filesize: 10M; post_max_size: 12M.
BOS Nginx client_max_body_size: 12m.
PHP-FPM limits apply to other sites sharing that configuration.
Verify changes with php-fpm8.5 -t and nginx -t before service reloads.
After restoring files, recheck ownership and private-storage permissions.

## Backups
Back up MariaDB and storage/app together with .env and the deployed commit.
Storage backups must include private workspace documents.
Backups contain sensitive information and require restricted access.
Archive validation is not a successful restore test.
Keep a secure off-server copy; retention and automation need separate setup.

Pre-documents application backup:
 /home/aceguard/bos-before-workspace-documents-cXEdcY0E
Upload configuration backup:
 /home/aceguard/bos-upload-config-8b7d3erl

## Recovery
Keep the application in maintenance mode while diagnosing deployment failure.
A code rollback may retain the new additive table; do not blindly roll back
migrations or discard uploaded documents.
Before any restore, make a fresh backup of the current database and files.
Choose a matching database/files/APP_KEY/commit recovery point.
Restore into an isolated environment first and verify login, MFA, workspace
access and document contents.
A production restore overwrites newer data and requires an explicit recovery
decision. Do not import an old database merely to fix a view or permission error.
Rebuild caches as above and verify the site before leaving maintenance mode.

## Staging
Staging mail uses the array mailer and does not deliver invitations.
For local preview, keep this server terminal open:
 php artisan serve --host=127.0.0.1 --port=8081
Access from a remote workstation requires SSH port forwarding.
Never expose staging by binding to all interfaces merely to fix a tunnel.

## Handover acceptance
- Production login and enrolled-account MFA work.
- Workspace access and member roles behave as intended.
- Invitation email arrives and acceptance works.
- Access history loads for administrators.
- Upload/download a harmless PDF or TXT; verify downloaded contents.
- Confirm document count increases and decreases after deletion.
- Verify a file larger than 2 MiB but within 10 MiB uploads successfully.
- Confirm a viewer can download but cannot upload or delete.
- Planned navigation does not behave as a working module.
