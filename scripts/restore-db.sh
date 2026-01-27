#!/bin/bash
#
# Restaurar un backup de MySQL (Docker producción)
# Uso:
#   chmod +x scripts/restore-db.sh
#   ./scripts/restore-db.sh ./backups/backup_escaner_total_prod_YYYYmmdd_HHMMSS.sql.gz
#
# Requiere:
# - Docker corriendo
# - Contenedor DB en ejecución (por defecto: escaner_total_db_prod)
# - Variables de entorno: DB_DATABASE, DB_USERNAME, DB_PASSWORD (y opcional MYSQL_ROOT_PASSWORD)
#
set -euo pipefail

BACKUP_PATH="${1:-}"

if [ -z "$BACKUP_PATH" ]; then
  echo "ERROR: Debes indicar la ruta del backup (.sql o .sql.gz)."
  echo "Ejemplo: ./scripts/restore-db.sh ./backups/backup_escaner_total_prod_20260101_020000.sql.gz"
  exit 1
fi

if [ ! -f "$BACKUP_PATH" ]; then
  echo "ERROR: No existe el archivo: $BACKUP_PATH"
  exit 1
fi

DB_CONTAINER_NAME="${DB_CONTAINER_NAME:-escaner_total_db_prod}"
DB_NAME="${DB_DATABASE:-escaner_total_prod}"
DB_USER="${DB_USERNAME:-escaner_user_prod}"
DB_PASSWORD="${DB_PASSWORD:-}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-}"

if ! docker ps --format '{{.Names}}' | grep -q "^${DB_CONTAINER_NAME}$"; then
  echo "ERROR: El contenedor ${DB_CONTAINER_NAME} no está corriendo."
  echo "Sugerencia: docker compose -f docker-compose.prod.yml ps"
  exit 1
fi

if [ -z "$DB_PASSWORD" ] && [ -z "$MYSQL_ROOT_PASSWORD" ]; then
  echo "ERROR: Debes configurar DB_PASSWORD o MYSQL_ROOT_PASSWORD en el entorno antes de restaurar."
  echo "Ejemplo: export DB_PASSWORD='...'"
  exit 1
fi

if [ "${CONFIRM_RESTORE:-}" != "YES" ]; then
  echo "ADVERTENCIA: Vas a RESTAURAR la base de datos '${DB_NAME}'."
  echo "Esto sobrescribirá datos si el dump contiene DROP/CREATE/INSERT."
  echo ""
  echo "Para continuar, ejecuta con: CONFIRM_RESTORE=YES $0 \"$BACKUP_PATH\""
  exit 1
fi

echo "=========================================="
echo "Restauración de Base de Datos"
echo "=========================================="
echo "Contenedor: $DB_CONTAINER_NAME"
echo "Base de datos: $DB_NAME"
echo "Backup: $BACKUP_PATH"
echo ""

MYSQL_PWD_ENV="${DB_PASSWORD:-$MYSQL_ROOT_PASSWORD}"
MYSQL_USER_TO_USE="$DB_USER"

# Si el usuario normal falla por permisos, el operador puede exportar MYSQL_USER_TO_USE=root
MYSQL_USER_TO_USE="${MYSQL_USER_TO_USE:-root}"

echo "Restaurando..."

if [[ "$BACKUP_PATH" == *.gz ]]; then
  gunzip -c "$BACKUP_PATH" | docker exec -i -e MYSQL_PWD="$MYSQL_PWD_ENV" "$DB_CONTAINER_NAME" mysql -u "$MYSQL_USER_TO_USE" "$DB_NAME"
else
  cat "$BACKUP_PATH" | docker exec -i -e MYSQL_PWD="$MYSQL_PWD_ENV" "$DB_CONTAINER_NAME" mysql -u "$MYSQL_USER_TO_USE" "$DB_NAME"
fi

echo ""
echo "✓ Restauración completada."
echo "=========================================="

