#!/bin/sh
#
# Container bootstrap. Idempotent on every start so `docker compose up` works
# from a clean slate without manual `cp .env.example .env`, key generation,
# migration, or seeding. Each step is guarded so subsequent restarts are
# fast and side-effect free.
#

set -e

cd /var/www/html

# 1. Materialise .env from the example on first boot. Container env vars
#    win at runtime regardless of what's in this file (Laravel reads real
#    env first, then .env), so this is purely a fallback.
if [ ! -f .env ]; then
    echo "[entrypoint] .env not present — copying from .env.example"
    cp .env.example .env
fi

# 2. Generate APP_KEY when missing or empty so the encrypter has something
#    to work with even on a fresh image.
if ! grep -qE '^APP_KEY=base64:' .env; then
    echo "[entrypoint] APP_KEY empty — generating"
    php artisan key:generate --force --ansi
fi

# 3. Generate JWT_SECRET when missing or empty (php-open-source-saver/jwt-auth
#    refuses to issue tokens otherwise).
if ! grep -qE '^JWT_SECRET=.+' .env; then
    echo "[entrypoint] JWT_SECRET empty — generating"
    php artisan jwt:secret --force --ansi
fi

# 4. Wait for MySQL to accept connections. `php artisan migrate` would
#    otherwise crash on a cold compose stack while the DB is still starting.
#    `migrate:status` exits 0 once tables exist OR exits with "Migration
#    table not found." once the connection works but the schema is empty
#    (which is the expected first-boot state). Treat both as ready.
echo "[entrypoint] waiting for database"
while true; do
    if output=$(php artisan migrate:status 2>&1); then
        break
    fi
    if printf '%s' "$output" | grep -qi "Migration table not found"; then
        break
    fi
    sleep 1
done

# 5. Migrations are idempotent by design.
echo "[entrypoint] running migrations"
php artisan migrate --force --ansi

# 6. Seed only when the users table is empty so a restart does not
#    re-insert demo accounts. `db:seed --force` would otherwise duplicate.
USER_COUNT=$(php artisan tinker --execute='echo \App\Models\User::query()->count();' 2>/dev/null | tail -n 1 | tr -dc '0-9')
if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
    echo "[entrypoint] seeding (users table empty)"
    php artisan db:seed --force --ansi
else
    echo "[entrypoint] skipping seed (${USER_COUNT} users already present)"
fi

# 7. Warm today's TCMB exchange rates so the storefront can render USD/EUR
#    prices on first hit. Idempotent — the migration's unique constraint
#    on (currency, fetched_at) blocks duplicates. Non-fatal: a TCMB blip
#    or offline host should not block the API from booting.
echo "[entrypoint] warming exchange rates"
php artisan currency:fetch --ansi || echo "[entrypoint] currency:fetch failed; rates may be stale (offline?)"

# 8. Hand off to the original CMD (php-fpm by default).
exec "$@"
