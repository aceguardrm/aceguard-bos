#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ "$PWD" != /home/aceguard/bos-mfa-staging ]]; then
    echo 'Run this only from the isolated /home/aceguard/bos-mfa-staging checkout.'
    exit 1
fi
if [[ -e .env ]]; then
    echo 'Staging already has an environment. Stopping to preserve it.'
    exit 1
fi
umask 077
cp .env.example .env
python3 - <<'PY'
from pathlib import Path
settings = {
 'APP_NAME':'"AceGuard BOS Staging"', 'APP_ENV':'local', 'APP_DEBUG':'false',
 'APP_URL':'http://127.0.0.1:8081', 'DB_CONNECTION':'sqlite',
 'DB_DATABASE':str(Path('database/staging.sqlite').resolve()), 'DB_URL':'null',
 'MAIL_MAILER':'array', 'QUEUE_CONNECTION':'sync', 'CACHE_STORE':'file',
 'SESSION_DRIVER':'file', 'SESSION_SECURE_COOKIE':'false', 'SESSION_DOMAIN':'null',
}
p=Path('.env')
lines=[l for l in p.read_text().splitlines() if l.split('=',1)[0].strip() not in settings]
p.write_text('\n'.join(lines)+'\n'+'\n'.join(k+'='+v for k,v in settings.items())+'\n')
Path('database/staging.sqlite').touch()
PY
composer install --no-interaction --prefer-dist
php artisan key:generate
php artisan migrate --force
npm ci
npm run build
php artisan tinker --execute='$password = \Laravel\Prompts\password("Choose a staging-only password (12+ characters)", required: true); if (strlen($password) < 12) { throw new \RuntimeException("Password must contain at least 12 characters. No account created."); } \App\Models\User::create(["name" => "BOS Staging", "email" => "staging@example.invalid", "password" => \Illuminate\Support\Facades\Hash::make($password)]); echo "Staging login: staging@example.invalid\n";'
echo 'Forward port 8081 in VS Code, then open http://127.0.0.1:8081/login locally.'
echo 'Press Ctrl+C to stop staging. This environment sends no emails.'
php artisan serve --host=127.0.0.1 --port=8081
