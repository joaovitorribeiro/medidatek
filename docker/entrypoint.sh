#!/usr/bin/env sh
set -e
umask 0002

cd /app

mkdir -p \
  /app/storage/app \
  /app/storage/app/public \
  /app/storage/app/public/landing \
  /app/storage/app/public/landing/bento \
  /app/storage/app/public/landing/uploads \
  /app/storage/app/public/projects \
  /app/storage/framework/cache/data \
  /app/storage/framework/sessions \
  /app/storage/framework/views \
  /app/storage/framework/testing \
  /app/storage/logs \
  /app/bootstrap/cache

migrate_media_dir() {
  from_dir="${1%/}"
  to_dir="${2%/}"

  if [ ! -d "$from_dir" ]; then
    return 0
  fi

  find "$from_dir" -type f | while IFS= read -r src_file; do
    rel_path="${src_file#$from_dir/}"
    target_file="$to_dir/$rel_path"
    target_dir="$(dirname "$target_file")"
    mkdir -p "$target_dir"

    if [ ! -f "$target_file" ]; then
      cp "$src_file" "$target_file"
    fi
  done
}

ensure_symlink() {
  target_path="$1"
  link_path="$2"
  link_parent="$(dirname "$link_path")"

  mkdir -p "$link_parent"

  if [ -L "$link_path" ] || [ -e "$link_path" ]; then
    rm -rf "$link_path"
  fi

  ln -s "$target_path" "$link_path"
}

# Migrate legacy media folders into canonical storage/app/public paths.
migrate_media_dir /app/public/midia/landing/bento /app/storage/app/public/landing/bento
migrate_media_dir /app/public/midia/projetos /app/storage/app/public/projects
migrate_media_dir /app/public/midia/projects /app/storage/app/public/projects
migrate_media_dir /app/storage/app/public/midia/landing/bento /app/storage/app/public/landing/bento
migrate_media_dir /app/storage/app/public/midia/projetos /app/storage/app/public/projects
migrate_media_dir /app/storage/app/public/midia/projects /app/storage/app/public/projects
migrate_media_dir /app/storage/app/public/projetos /app/storage/app/public/projects

# Keep legacy public URLs working while files stay canonical in storage.
mkdir -p /app/public/midia /app/public/midia/landing
ensure_symlink /app/storage/app/public/landing/bento /app/public/midia/landing/bento
ensure_symlink /app/storage/app/public/landing/uploads /app/public/midia/landing/uploads
ensure_symlink /app/storage/app/public/projects /app/public/midia/projetos
ensure_symlink /app/storage/app/public/projects /app/public/midia/projects

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R ug+rwX /app/storage /app/bootstrap/cache
find /app/storage /app/bootstrap/cache -type d -exec chmod 2775 {} \;
find /app/storage /app/bootstrap/cache -type f -exec chmod 664 {} \;

APP_URL_EFFECTIVE="${APP_URL:-${COOLIFY_URL:-http://localhost}}"
APP_NAME_EFFECTIVE="${APP_NAME:-MedidaTek}"
APP_ENV_EFFECTIVE="${APP_ENV:-production}"
APP_DEBUG_EFFECTIVE="${APP_DEBUG:-false}"
APP_KEY_EFFECTIVE="${APP_KEY:-}"
CACHE_STORE_EFFECTIVE="${CACHE_STORE:-redis}"
QUEUE_CONNECTION_EFFECTIVE="${QUEUE_CONNECTION:-redis}"
REDIS_URL_EFFECTIVE="${REDIS_URL:-}"
REDIS_HOST_EFFECTIVE="${REDIS_HOST:-}"
REDIS_USERNAME_EFFECTIVE="${REDIS_USERNAME:-}"
REDIS_PASSWORD_EFFECTIVE="${REDIS_PASSWORD:-}"
REDIS_CLIENT_EFFECTIVE="${REDIS_CLIENT:-predis}"

APP_URL_EFFECTIVE="$(printf '%s' "$APP_URL_EFFECTIVE" | tr -d '\r' | sed -e 's/^[[:space:]]*//;s/[[:space:]]*$//' -e 's/`//g')"
export APP_URL="$APP_URL_EFFECTIVE"

if [ "$REDIS_URL_EFFECTIVE" = "null" ]; then REDIS_URL_EFFECTIVE=""; fi
if [ "$REDIS_HOST_EFFECTIVE" = "null" ]; then REDIS_HOST_EFFECTIVE=""; fi
if [ "$REDIS_USERNAME_EFFECTIVE" = "null" ]; then REDIS_USERNAME_EFFECTIVE=""; fi
if [ "$REDIS_PASSWORD_EFFECTIVE" = "null" ]; then REDIS_PASSWORD_EFFECTIVE=""; fi

SESSION_COOKIE_EFFECTIVE="${SESSION_COOKIE:-}"
SESSION_COOKIE_EFFECTIVE="$(printf '%s' "$SESSION_COOKIE_EFFECTIVE" | tr -d '\r' | sed -e 's/^[[:space:]]*//;s/[[:space:]]*$//' -e 's/`//g')"
if [ "$SESSION_COOKIE_EFFECTIVE" = "null" ]; then SESSION_COOKIE_EFFECTIVE=""; fi
if [ -z "$SESSION_COOKIE_EFFECTIVE" ]; then
  SESSION_COOKIE_EFFECTIVE="$(php -r '$n=getenv("APP_NAME") ?: "laravel"; $n=strtolower($n); $n=preg_replace("/[^a-z0-9]+/","_",$n); $n=trim($n,"_"); if($n==="") $n="laravel"; echo $n."_session";')"
fi

export SESSION_COOKIE="$SESSION_COOKIE_EFFECTIVE"

if [ "$APP_ENV_EFFECTIVE" = "production" ] && [ "$REDIS_CLIENT_EFFECTIVE" = "phpredis" ]; then
  if ! php -r 'exit(extension_loaded("redis") ? 0 : 1);'; then
    REDIS_CLIENT_EFFECTIVE="predis"
  fi
fi

if [ -z "$APP_KEY_EFFECTIVE" ]; then
  if [ "$APP_ENV_EFFECTIVE" = "production" ]; then
    echo "APP_KEY is required (set it in Coolify env vars)." >&2
    exit 1
  fi
  APP_KEY_EFFECTIVE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
fi

if [ "$APP_ENV_EFFECTIVE" = "production" ]; then
  if [ ! -f /app/public/build/manifest.json ] && [ ! -f /app/public/hot ]; then
    echo "Vite assets missing: /app/public/build/manifest.json (rebuild image with Vite build enabled)." >&2
    exit 1
  fi
  if { [ "$CACHE_STORE_EFFECTIVE" = "redis" ] || [ "$QUEUE_CONNECTION_EFFECTIVE" = "redis" ]; } && [ -z "$REDIS_URL_EFFECTIVE" ] && [ -z "$REDIS_HOST_EFFECTIVE" ]; then
    echo "Redis is required (set REDIS_URL or REDIS_HOST) when CACHE_STORE/QUEUE_CONNECTION use redis." >&2
    exit 1
  fi
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
SESSION_COOKIE=${SESSION_COOKIE_EFFECTIVE}

CACHE_STORE=${CACHE_STORE:-redis}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}

REDIS_CLIENT=${REDIS_CLIENT_EFFECTIVE}
REDIS_URL=${REDIS_URL_EFFECTIVE}
REDIS_HOST=${REDIS_HOST_EFFECTIVE}
REDIS_PORT=${REDIS_PORT:-6379}
REDIS_USERNAME=${REDIS_USERNAME_EFFECTIVE}
REDIS_PASSWORD=${REDIS_PASSWORD_EFFECTIVE}

VITE_APP_NAME=${VITE_APP_NAME:-${APP_NAME_EFFECTIVE}}
EOF

if [ ! -L /app/public/storage ]; then
  rm -rf /app/public/storage || true
  php artisan storage:link --no-interaction || true
fi

if [ "${WAIT_FOR_REDIS:-1}" = "1" ]; then
  REDIS_URL="$REDIS_URL_EFFECTIVE" REDIS_HOST="$REDIS_HOST_EFFECTIVE" REDIS_PORT="${REDIS_PORT:-6379}" php -r '
    $url = getenv("REDIS_URL") ?: "";
    $host = getenv("REDIS_HOST") ?: "";
    $port = (int) (getenv("REDIS_PORT") ?: 6379);
    $scheme = "tcp";

    if ($url !== "") {
      $parts = parse_url($url);
      if (is_array($parts)) {
        if (!empty($parts["scheme"]) && in_array($parts["scheme"], ["rediss", "tls"], true)) {
          $scheme = "tls";
        }
        if (!empty($parts["host"])) $host = $parts["host"];
        if (!empty($parts["port"])) $port = (int) $parts["port"];
      }
    }

    if ($host === "") $host = "redis";

    $tries = 60;
    while ($tries-- > 0) {
      $fp = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 1.5);
      if ($fp) {
        fclose($fp);
        exit(0);
      }
      usleep(500000);
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
