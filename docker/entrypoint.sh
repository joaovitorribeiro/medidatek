#!/usr/bin/env sh
set -e

cd /var/www/html

mkdir -p \
  /var/www/html/storage/framework/cache/data \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/framework/testing \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

SHARED_ENV="/var/www/html/storage/.env"

DESIRED_DB_CONNECTION="${DB_CONNECTION:-mysql}"

if [ -f "$SHARED_ENV" ] && [ ! -f .env ]; then
  EXISTING_DB_CONNECTION="$(grep -E '^DB_CONNECTION=' "$SHARED_ENV" 2>/dev/null | head -n 1 | cut -d= -f2-)"
  if [ -n "$EXISTING_DB_CONNECTION" ] && [ "$EXISTING_DB_CONNECTION" != "$DESIRED_DB_CONNECTION" ]; then
    rm -f "$SHARED_ENV"
  else
    cp "$SHARED_ENV" .env
  fi
fi

if [ ! -f .env ]; then
  cat > .env <<EOF
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-local}
APP_KEY=
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-medidatek}
DB_USERNAME=${DB_USERNAME:-medidatek}
DB_PASSWORD=${DB_PASSWORD:-medidatek}

SESSION_DRIVER=${SESSION_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}

VITE_APP_NAME=${VITE_APP_NAME:-${APP_NAME:-Laravel}}
EOF
fi

if [ ! -f "$SHARED_ENV" ]; then
  cp .env "$SHARED_ENV"
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
  if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --no-interaction
    cp .env "$SHARED_ENV"
  fi
fi

DB_PATH="${DB_DATABASE:-/var/www/html/storage/database.sqlite}"
if [ "${DB_CONNECTION:-mysql}" = "sqlite" ]; then
  mkdir -p "$(dirname "$DB_PATH")"
  if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
  fi
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $db   = getenv("DB_DATABASE") ?: "medidatek";
    $user = getenv("DB_USERNAME") ?: "medidatek";
    $pass = getenv("DB_PASSWORD") ?: "medidatek";
    $dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $tries = 60;
    while ($tries-- > 0) {
      try {
        new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
        exit(0);
      } catch (Throwable $e) {
        usleep(500000);
      }
    }
    fwrite(STDERR, "MySQL not ready\n");
    exit(1);
  ';
fi

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  php artisan migrate --force --no-interaction || true
fi

exec "$@"
