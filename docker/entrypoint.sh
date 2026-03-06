#!/usr/bin/env sh
set -e

cd /app

mkdir -p \
  /app/storage/app \
  /app/storage/app/public \
  /app/storage/framework/cache/data \
  /app/storage/framework/sessions \
  /app/storage/framework/views \
  /app/storage/framework/testing \
  /app/storage/logs \
  /app/bootstrap/cache

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R ug+rwX /app/storage /app/bootstrap/cache

APP_URL_EFFECTIVE="${APP_URL:-${COOLIFY_URL:-http://localhost}}"
APP_NAME_EFFECTIVE="${APP_NAME:-MedidaTek}"
APP_ENV_EFFECTIVE="${APP_ENV:-production}"
APP_DEBUG_EFFECTIVE="${APP_DEBUG:-false}"
APP_KEY_EFFECTIVE="${APP_KEY:-}"

if [ -z "$APP_KEY_EFFECTIVE" ]; then
  if [ "$APP_ENV_EFFECTIVE" = "production" ]; then
    echo "APP_KEY is required (set it in Coolify env vars)." >&2
    exit 1
  fi
  APP_KEY_EFFECTIVE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
fi

cat > /app/.env <<EOF
APP_NAME=${APP_NAME_EFFECTIVE}
APP_ENV=${APP_ENV_EFFECTIVE}
APP_KEY=${APP_KEY_EFFECTIVE}
APP_DEBUG=${APP_DEBUG_EFFECTIVE}
APP_URL=${APP_URL_EFFECTIVE}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-warning}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-${MYSQL_DATABASE:-medidatek}}
DB_USERNAME=${DB_USERNAME:-${MYSQL_USER:-medidatek}}
DB_PASSWORD=${DB_PASSWORD:-${MYSQL_PASSWORD:-medidatek}}

SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_ENCRYPT=${SESSION_ENCRYPT:-false}

CACHE_STORE=${CACHE_STORE:-redis}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}

REDIS_CLIENT=${REDIS_CLIENT:-phpredis}
REDIS_URL=${REDIS_URL:-}
REDIS_HOST=${REDIS_HOST:-}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_USERNAME=${REDIS_USERNAME:-}
REDIS_PASSWORD=${REDIS_PASSWORD:-}

VITE_APP_NAME=${VITE_APP_NAME:-${APP_NAME_EFFECTIVE}}
EOF

if [ "${WAIT_FOR_REDIS:-1}" = "1" ]; then
  php -r '
    if (!class_exists("Redis")) {
      exit(0);
    }
    $url = getenv("REDIS_URL") ?: "";
    $host = getenv("REDIS_HOST") ?: "";
    $port = (int) (getenv("REDIS_PORT") ?: 6379);
    $user = getenv("REDIS_USERNAME");
    $pass = getenv("REDIS_PASSWORD");
    if ($url !== "") {
      $parts = parse_url($url);
      if (is_array($parts)) {
        if (!empty($parts["host"])) $host = $parts["host"];
        if (!empty($parts["port"])) $port = (int) $parts["port"];
        if (array_key_exists("user", $parts)) $user = $parts["user"];
        if (array_key_exists("pass", $parts)) $pass = $parts["pass"];
      }
    }
    if ($host === "") {
      $host = "redis";
    }
    $tries = 60;
    while ($tries-- > 0) {
      try {
        $r = new Redis();
        $r->connect($host, $port, 1.5);
        if ($user !== false && $user !== "" && $pass !== false && $pass !== "") {
          $r->auth([$user, $pass]);
        } elseif ($pass !== false && $pass !== "") {
          $r->auth($pass);
        }
        $r->ping();
        exit(0);
      } catch (Throwable $e) {
        usleep(500000);
      }
    }
    fwrite(STDERR, "Redis not ready\n");
    exit(1);
  ';
fi

if [ "${WAIT_FOR_DB:-1}" = "1" ] && [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $db   = getenv("DB_DATABASE") ?: (getenv("MYSQL_DATABASE") ?: "medidatek");
    $user = getenv("DB_USERNAME") ?: (getenv("MYSQL_USER") ?: "medidatek");
    $pass = getenv("DB_PASSWORD") ?: (getenv("MYSQL_PASSWORD") ?: "medidatek");
    $dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $tries = 90;
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

if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
  php artisan migrate --force --no-interaction
fi

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
  php artisan app:ensure-admin || true
fi

if [ "${RUN_OPTIMIZE:-1}" = "1" ]; then
  php artisan optimize
fi

exec "$@"
