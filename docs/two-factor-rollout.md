# Two-factor authentication rollout

Status: draft PR #2; production unchanged.

Base: release/v0.2.0 at bec0a82. Branch: feature/two-factor-authentication.

Fortify dependencies are locked. The 39-test suite passed (124 assertions), including real OTP enrollment, recovery-code replacement, disabled registration and password reset. Frontend compilation and Laravel route/view caching passed in GitHub Actions.

Follow-up: atomic compare-and-swap recovery-code consumption rejects stale concurrent requests. Added regression tests for stale recovery attempts and remember-me login. Remember-me deliberately retains Laravel's persistent-session behavior after successful MFA; it does not skip MFA on initial credential login. Existing sessions are not forcibly revoked by enrollment.

## Isolated staging

Create /home/aceguard/bos-mfa-staging as a separate Git worktree from the feature branch. Run bash scripts/start-mfa-staging.sh there. The script refuses any other directory or an existing .env. It creates a fresh SQLite database, unique app key and staging-only user, uses an in-memory mail transport, and listens only on 127.0.0.1:8081. Never copy production .env or production data into staging.

Forward port 8081 with VS Code Remote SSH or an SSH tunnel. Login as staging@example.invalid with the staging-only password chosen during setup. Visit /account/security and confirm the password.

Check desktop and mobile layouts; scan the QR code; confirm enrollment with a real authenticator code; save staging recovery codes privately. Log out and test invalid code, valid code, and recovery-code login. Verify a consumed recovery code fails. Replace recovery codes and verify an old code fails. Verify ordinary login after disabling MFA. Do not post QR codes, setup keys, passwords or recovery codes in screenshots.

Stopping the server with Ctrl+C preserves the staging environment. Restart from the staging directory with php artisan serve --host=127.0.0.1 --port=8081.

## Production gate

Complete staging/browser verification before merge. Back up production database and environment outside the web root. Install locked dependencies, run the additive migration, build frontend assets, rebuild caches and verify ordinary login. Enable MFA interactively and store recovery codes privately. Do not roll back to password-only code after enrollment without an account recovery plan.
