# Two-factor authentication rollout

Status: draft; not approved for production deployment.

Base: release/v0.2.0 at bec0a82. Branch: feature/two-factor-authentication.

Implemented: Fortify login pipeline; opt-in authenticator confirmation; password-confirmed security settings; recovery codes; throttled challenges; hidden encrypted secrets; no-store settings response. Existing BOS password reset and disabled registration routes remain application-owned.

Outstanding gates:
- Resolve laravel/fortify with Composer and commit the resulting composer.lock. The current lock does not yet include Fortify. Do not run composer install against this draft as a deployment.
- Run the complete test suite in an isolated database; inspect new authentication tests and validate route/view caching.
- Visually verify setup, confirmed setup, failed challenge, successful challenge and recovery-code login. Confirm old recovery codes fail after regeneration.
- Review remembered-login behavior and concurrent recovery-code consumption before production acceptance.
- Verify a valid code with a real authenticator in staging and store recovery codes privately.

The draft GitHub validation workflow resolves the dependency and publishes composer.lock as an artifact for review. After importing the lock, replace its composer update step with composer install and rerun validation against the pinned dependencies.

Only after gates pass: back up production database and environment outside the web root, install locked dependencies, run additive migration, rebuild caches and verify ordinary login. Enable MFA interactively at /account/security only after saving recovery codes. Do not roll back to password-only code after users enroll without a recovery plan.

Workspace validation: seven changed PHP source/test files parsed with php-parser; git diff --check passed. PHP and Composer are unavailable locally. Remote publication was blocked by automatic approval review and requires explicit user approval; no live server or account changes were made.
