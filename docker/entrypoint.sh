#!/usr/bin/env sh
set -eu

cd /var/www/html

echo "[docker] Iniciando contenedor..."

# Crear .env desde template docker si no existe
if [ ! -f ".env" ]; then
  if [ -f "docker/env.example" ]; then
    echo "[docker] Creando .env desde docker/env.example"
    cp docker/env.example .env
  else
    echo "[docker] ERROR: no existe .env ni docker/env.example"
    exit 1
  fi
fi

# Esperar MySQL (si se configuró DB_HOST)
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "[docker] Esperando MySQL en ${DB_HOST}:${DB_PORT} ..."
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

# Key
if ! php artisan key:show >/dev/null 2>&1; then
  echo "[docker] Generando APP_KEY..."
  php artisan key:generate --force
fi

# Storage link (para imágenes / evidencias)
if [ ! -e "public/storage" ]; then
  echo "[docker] Creando storage:link..."
  php artisan storage:link || true
fi

# Migraciones/seeders opcionales
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "[docker] Ejecutando migraciones..."
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  SEEDER_CLASS="${SEEDER_CLASS:-Database\\Seeders\\DatabaseSeeder}"
  # Normalizar: si viene con doble backslash (\\) dejarlo como (\)
  SEEDER_CLASS="$(printf '%s' "$SEEDER_CLASS" | sed 's/\\\\\\\\/\\\\/g')"
  echo "[docker] Ejecutando seeders (${SEEDER_CLASS})..."
  php artisan db:seed --force --class="${SEEDER_CLASS}" || true
fi

# Iniciar cron para Laravel Scheduler (si está instalado)
if command -v cron >/dev/null 2>&1; then
    echo "[docker] Iniciando cron para Laravel Scheduler..."
    service cron start
fi

echo "[docker] Listo. Levantando servidor web..."
exec "$@"


