#!/usr/bin/env bash
#
# Ejecuta el test de ciclos de visitante EN PRODUCCIÓN desde el host,
# sin crear ningún contenedor nuevo. Usa la BD real (DatabaseTransactions
# hace rollback, pero pueden haber saltos en AUTO_INCREMENT).
#
# Requisitos: PHP 8.1+ y Composer en el servidor (no dentro de Docker).
#
# Uso (desde la raíz del proyecto en el servidor):
#   cd /var/www/escaner-total
#   bash scripts/run-test-ciclos-produccion.sh
#
# Opcional: forzar throttle para reproducir 429:
#   TEST_KEEP_THROTTLE=1 bash scripts/run-test-ciclos-produccion.sh
#

set -e
cd "$(dirname "$0")/.."
PROJECT_ROOT="$(pwd)"

echo "==> Proyecto: $PROJECT_ROOT"

# Cargar .env (export solo las que necesitamos para no pisar todo)
if [ -f .env ]; then
  set -a
  source .env 2>/dev/null || true
  set +a
fi

# Desde el HOST nos conectamos a MySQL por el puerto expuesto (no por servicio "db")
export DB_HOST="${DB_HOST_FOR_HOST:-127.0.0.1}"
export DB_PORT="${DB_EXTERNAL_PORT:-3307}"
export APP_ENV=testing
export ACCESS_DEVICE_KEY="${ACCESS_DEVICE_KEY:-1234abcd}"

echo "==> DB: $DB_HOST:$DB_PORT / $DB_DATABASE"
echo ""

# Composer install (incluye dev para phpunit)
if [ ! -f vendor/autoload.php ] || [ ! -d vendor/phpunit ]; then
  echo "==> Instalando dependencias (composer install)..."
  composer install --no-interaction
else
  echo "==> vendor/ ya existe. Para reinstalar dev: composer install"
fi

echo ""
echo "==> Ejecutando test de ciclos (VisitanteAccesoCiclosTest)..."
php artisan test --filter=VisitanteAccesoCiclosTest

echo ""
echo "==> Log detallado (si existe): storage/logs/visitante-acceso-ciclos-test.log"
if [ -f storage/logs/visitante-acceso-ciclos-test.log ]; then
  echo "    Últimas líneas:"
  tail -n 5 storage/logs/visitante-acceso-ciclos-test.log
fi
