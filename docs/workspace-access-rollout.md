# Workspace access: first delivery

Status: development; not deployed. Production MFA was verified by Ray on 29 September 2026: authenticator login, recovery login and rejection of recovery-code reuse.

## Model and scope

Existing `clients` are organisation workspaces. A user may have memberships in several workspaces. This phase protects the existing workspace, project, task, security and Business Pulse routes; it does not add a separate customer CRM hierarchy within a company.

- Viewer: read assigned workspaces and their records.
- Editor: viewer rights plus project/task/security/assessment changes.
- Administrator: editor rights plus editing workspace details.
- Platform administrator: all workspaces, including creating/deleting workspaces. Only an operator command grants this role; it is not mass assignable.

Unassigned users have an empty portfolio. Unauthorised record URLs return 404; insufficient write permissions return 403. Lists, metrics, project selectors and dashboard counts use membership filters. Nested task/control ownership remains checked. Submitted project destinations require edit access. Existing HTTP routes are covered; future routes, jobs and exports must explicitly apply access scoping. There is no model-wide ambient authentication scope.

Public registration stays disabled. This first delivery assigns roles to existing users via a server command. Self-service invitations, a membership-management interface and an audit trail are still separate work before customer onboarding. No new customer accounts are provisioned by this change.

## Staging gate

Use an isolated database and application key, never production credentials. Apply migrations, explicitly grant the staging operator platform access, and prepare two separate workspaces and test users. Verify viewer/editor/administrator behavior, direct URL denial, portfolio isolation and successful existing MFA login. Automated tests cover these boundaries and recovery behavior. Do not copy production MFA secrets into staging.

## Deployment gate

Review the PR and passing CI first. Take a fresh database/configuration/upload backup after the now-completed production MFA enrollment. The earlier pre-MFA backup is useful historical recovery material but does not contain the enrolled MFA state.

Deploy in maintenance mode. After migrating and before reopening the site, grant Ray platform administration explicitly:

```sh
php artisan bos:access admin@aceguard.co.uk --platform-admin
```

The migration intentionally grants nobody access automatically. Failing to run the grant leaves accounts authenticated but without workspace access. Verify the intended account before running it. Do not grant every user platform access.

Assign an existing account to a particular workspace (replace the example email and ID):

```sh
php artisan bos:access member@example.invalid --workspace=123 --role=viewer
```

Roles are `administrator`, `editor`, `viewer`. The same command updates a role. Add `--revoke` to remove membership. `--platform-admin --revoke` removes global platform access, leaving any explicit memberships intact. Commands require trusted server access and do not transmit email or expose passwords.

Verify Ray's portfolio and MFA login after deployment. Keep a second authenticated administrative session during checks. Do not roll back to unscoped code while customer accounts can sign in; use maintenance mode first. Never roll back the MFA migration as part of this change.
