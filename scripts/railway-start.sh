#!/usr/bin/env sh
set -e

# Set defaults for Railway deployment
# These should be set in Railway Variables, but provide sensible defaults
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="true"
export APP_URL="${APP_URL:-https://amiga-travel-production.up.railway.app}"
export APP_NAME="${APP_NAME:-Amiga Gracia}"
export SESSION_DRIVER="${SESSION_DRIVER:-database}"
export CACHE_STORE="${CACHE_STORE:-database}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"

# Database - fallback to Railway MYSQL env vars if DB_* not set explicitly
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-${MYSQL_HOST:-sakura.proxy.rlwy.net}}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-${MYSQL_PORT:-43993}}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-${MYSQL_DATABASE:-railway}}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-${MYSQL_USER:-root}}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-${MYSQL_ROOT_PASSWORD:-BIMPMSZRxyaizrljoaKdBoAixcTWShuP}}}"

# Mail settings
export MAIL_MAILER="${MAIL_MAILER:-smtp}"
export MAIL_HOST="${MAIL_HOST:-smtp.gmail.com}"
export MAIL_PORT="${MAIL_PORT:-587}"
export MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
export MAIL_USERNAME="${MAIL_USERNAME}"
export MAIL_PASSWORD="${MAIL_PASSWORD}"
export MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
export RESEND_API_KEY="${RESEND_API_KEY}"

export NOCAPTCHA_SITEKEY="${NOCAPTCHA_SITEKEY}"
export NOCAPTCHA_SECRET="${NOCAPTCHA_SECRET}"
export MAIL_FROM_NAME="${MAIL_FROM_NAME}"
export MAIL_SCHEME="${MAIL_SCHEME}"

# Handle Firebase Credentials safely to avoid .env parsing errors
if [ -n "$FIREBASE_CREDENTIALS" ]; then
    echo "=== Writing Firebase credentials via PHP parser ==="
    # Use PHP to properly parse the JSON blob (handles actual newlines in private_key)
    # and write a clean, valid JSON file.
    php -r "
\$raw = getenv('FIREBASE_CREDENTIALS');
// Strip surrounding double-quotes Railway may add
\$raw = trim(\$raw);
if (isset(\$raw[0]) && \$raw[0] === '\"') { \$raw = substr(\$raw, 1); }
if (strlen(\$raw) > 0 && substr(\$raw, -1) === '\"') { \$raw = substr(\$raw, 0, -1); }

// First try: parse as-is
\$decoded = json_decode(\$raw, true);

// Second try: replace actual newlines with \\n escape sequences
if (!\$decoded) {
    \$fixed = str_replace(\"\\n\", \"\\\\n\", \$raw);
    \$decoded = json_decode(\$fixed, true);
}

// Third try: replace \\\\n with \\n (double-escaped)
if (!\$decoded) {
    \$fixed2 = str_replace('\\\\n', \"\\n\", \$raw);
    \$decoded = json_decode(\$fixed2, true);
}

if (is_array(\$decoded)) {
    file_put_contents('/var/www/html/storage/firebase-auth.json', json_encode(\$decoded));
    echo 'Firebase: credentials written as valid JSON (' . strlen(json_encode(\$decoded)) . ' bytes)' . PHP_EOL;
} else {
    // Fallback: write raw content (Firebase SDK will report the parse error)
    file_put_contents('/var/www/html/storage/firebase-auth.json', \$raw);
    echo 'Firebase WARNING: could not parse as JSON (' . json_last_error_msg() . '), wrote raw content' . PHP_EOL;
}
"
    export FIREBASE_CREDENTIALS_PATH="/var/www/html/storage/firebase-auth.json"
    # CRITICAL: unset the raw JSON blob from the process environment.
    # phpdotenv does NOT override existing env vars, so if FIREBASE_CREDENTIALS
    # is still set as the blob, env('FIREBASE_CREDENTIALS') would return the blob
    # instead of the file path we set in .env. Unsetting forces phpdotenv to use
    # the file path value from .env at runtime.
    unset FIREBASE_CREDENTIALS
    echo "=== FIREBASE_CREDENTIALS unset from process env (PHP-FPM will use .env value) ==="
else
    echo "=== WARNING: FIREBASE_CREDENTIALS env var is empty! Push notifications will not work. ==="
    export FIREBASE_CREDENTIALS_PATH=""
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
  echo "=== APP_KEY not found in environment, generating one... ==="
  # Write a temporary .env so key:generate has something to modify
  echo "APP_KEY=" > /var/www/html/.env
  php artisan key:generate --force --no-ansi 2>&1 || true
  # Read the freshly generated key back
  if [ -f /var/www/html/.env ]; then
    APP_KEY=$(grep "^APP_KEY=" /var/www/html/.env | sed 's/^APP_KEY=//')
  fi
  echo "=== Generated APP_KEY: ${APP_KEY:0:20}... ==="
fi

# Validate APP_KEY is not empty before proceeding
if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is still empty after generation attempt. Set APP_KEY in Railway Variables!" >&2
fi

# Create .env file in container from Railway environment variables
# This overrides any local .env that was copied into the image.
# IMPORTANT: APP_KEY must be set as a Railway Variable to persist across deploys.
#
# The writer lives in a standalone PHP file (scripts/write_env.php) so we NEVER
# have to embed PHP inside a shell heredoc. Heredocs with embedded PHP that
# contain escaped single/double quotes, regex backslashes, or inline comments
# with quote characters are prone to POSIX sh parse errors ("unterminated quoted
# string") depending on the shell flavour / SSH layer. Keeping the writer in a
# real .php file eliminates the entire quoting class of bugs.
#
# The writer also aggressively sanitizes fields that Railway users commonly
# paste with markdown backticks or stray whitespace: APP_URL, APP_NAME, hosts,
# credentials.
php /var/www/html/scripts/write_env.php

echo "=== .env regeneration complete ==="

# Dynamically configure Nginx to listen on Railway's assigned $PORT
PORT="${PORT:-10000}"
echo "=== Configuring Nginx port to $PORT ==="
sed -i "s/listen [0-9]*;/listen ${PORT};/g" /etc/nginx/http.d/default.conf 2>/dev/null || true

# Run migrations and setup
timeout 60 php artisan migrate --force --no-interaction || echo "Migrations skipped or timed out"

# Ensure all required storage subdirectories exist (important when using Railway Persistent Volume)
# These are wiped if the volume is freshly mounted, so we recreate them every startup
mkdir -p /var/www/html/storage/app/public/tickets
mkdir -p /var/www/html/storage/app/public/proofs
mkdir -p /var/www/html/storage/app/public/receipts
mkdir -p /var/www/html/storage/app/public/rebooking_proofs
mkdir -p /var/www/html/storage/app/public/livewire-tmp
mkdir -p /var/www/html/storage/app/private/livewire-tmp
mkdir -p /var/www/html/storage/app/livewire-tmp
mkdir -p /var/www/html/storage/app/acknowledgements
mkdir -p /var/www/html/storage/app/tickets
mkdir -p /var/www/html/storage/app/private
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage || true
chmod -R 775 /var/www/html/storage || true

# Remove stale symlink and recreate (critical when volume is mounted fresh)
rm -f /var/www/html/public/storage
php artisan storage:link || true

echo "=== Reached config cache step ==="
php artisan clear-compiled || true
php artisan config:clear || true
php artisan config:cache || true
php artisan route:clear || true
php artisan view:cache || true
php artisan event:clear || true
php artisan package:discover --ansi || true

# Reload PHP-FPM workers so the running processes pick up the freshly
# regenerated .env, config cache, and service manifests. Without this
# reload, long-running FPM children in production can serve with stale
# cached state for minutes/hours even after the files on disk are fixed.
if command -v supervisorctl >/dev/null 2>&1; then
  echo "=== Reloading PHP-FPM via supervisorctl ==="
  supervisorctl reread 2>/dev/null || true
  supervisorctl update 2>/dev/null || true
  supervisorctl restart php-fpm 2>/dev/null || supervisorctl restart all 2>/dev/null || echo "(supervisorctl restart skipped or failed)"
fi

echo "=== Starting Supervisor (Nginx + PHP-FPM + Queue Worker) ==="
exec supervisord -c /var/www/html/supervisord.conf
