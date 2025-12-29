#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "[docker-production] Iniciando contenedor de producción..."

# Verificar que .env existe
if [ ! -f ".env" ]; then
  echo "[docker-production] ERROR: El archivo .env no existe. Debes crearlo antes de iniciar producción."
  exit 1
fi

# Verificar que APP_ENV es production
if [ "${APP_ENV:-}" != "production" ]; then
  echo "[docker-production] ADVERTENCIA: APP_ENV no está configurado como 'production'"
fi

# Verificar que APP_DEBUG es false
if [ "${APP_DEBUG:-true}" = "true" ]; then
  echo "[docker-production] ADVERTENCIA: APP_DEBUG está en 'true'. Debe ser 'false' en producción."
fi

# Esperar MySQL
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "[docker-production] Esperando MySQL en ${DB_HOST}:${DB_PORT} ..."
php -r '
$host = getenv("DB_HOST") ?: "db";
$port = (int) (getenv("DB_PORT") ?: 3306);
$max = 60;
$start = time();
while (true) {
  $fp = @fsockopen($host, $port, $errno, $errstr, 1);
  if ($fp) { fclose($fp); break; }
  if (time() - $start > $max) { fwrite(STDERR, "Timeout esperando MySQL\n"); exit(1); }
  usleep(500000);
}
'

# Verificar/generar APP_KEY
if ! php artisan key:show >/dev/null 2>&1; then
  echo "[docker-production] Generando APP_KEY..."
  php artisan key:generate --force
fi

# Storage link
if [ ! -e "public/storage" ]; then
  echo "[docker-production] Creando storage:link..."
  php artisan storage:link || true
fi

# Verificar permisos
echo "[docker-production] Verificando permisos..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Optimizaciones de Laravel (solo en producción)
if [ "${APP_ENV:-}" = "production" ]; then
  echo "[docker-production] Optimizando Laravel para producción..."

  # Limpiar cachés antiguos
  php artisan optimize:clear || true

  # Optimizar
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
  php artisan event:cache || true
fi

# Migraciones/seeders (solo si están explícitamente habilitados)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "[docker-production] ADVERTENCIA: Ejecutando migraciones automáticamente..."
  php artisan migrate --force
else
  echo "[docker-production] Migraciones automáticas deshabilitadas (RUN_MIGRATIONS=false)"
  echo "[docker-production] Ejecuta manualmente: docker compose exec app php artisan migrate --force"
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  echo "[docker-production] ADVERTENCIA: Ejecutando seeders automáticamente..."
  SEEDER_CLASS="${SEEDER_CLASS:-Database\\Seeders\\DatabaseSeeder}"
  SEEDER_CLASS="$(printf '%s' "$SEEDER_CLASS" | sed 's/\\\\\\\\/\\\\/g')"
  php artisan db:seed --force --class="${SEEDER_CLASS}" || true
else
  echo "[docker-production] Seeders automáticos deshabilitados (RUN_SEEDERS=false)"
fi

echo "[docker-production] Contenedor listo. Iniciando servidor web..."
exec "$@"

