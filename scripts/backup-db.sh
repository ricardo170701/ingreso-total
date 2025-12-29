#!/bin/bash

# Script de backup de base de datos para producción
# Uso: ./scripts/backup-db.sh

set -e

# Configuración
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)
CONTAINER_NAME="escaner_total_db_prod"
DB_NAME="${DB_DATABASE:-escaner_total_prod}"
DB_USER="${DB_USERNAME:-escaner_user_prod}"
DB_PASSWORD="${DB_PASSWORD}"

# Crear directorio de backups si no existe
mkdir -p "$BACKUP_DIR"

# Nombre del archivo de backup
BACKUP_FILE="$BACKUP_DIR/backup_${DB_NAME}_${DATE}.sql"

echo "=========================================="
echo "Backup de Base de Datos"
echo "=========================================="
echo "Base de datos: $DB_NAME"
echo "Archivo: $BACKUP_FILE"
echo ""

# Verificar que el contenedor está corriendo
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo "ERROR: El contenedor $CONTAINER_NAME no está corriendo"
    exit 1
fi

# Realizar backup
echo "Realizando backup..."
docker exec "$CONTAINER_NAME" mysqldump \
    -u "$DB_USER" \
    -p"$DB_PASSWORD" \
    --single-transaction \
    --routines \
    --triggers \
    "$DB_NAME" > "$BACKUP_FILE"

# Comprimir backup
if [ -f "$BACKUP_FILE" ]; then
    echo "Comprimiendo backup..."
    gzip "$BACKUP_FILE"
    BACKUP_FILE="${BACKUP_FILE}.gz"
    echo "✓ Backup completado: $BACKUP_FILE"

    # Mostrar tamaño
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "  Tamaño: $SIZE"
else
    echo "ERROR: No se pudo crear el backup"
    exit 1
fi

# Limpiar backups antiguos (mantener últimos 30 días)
echo ""
echo "Limpiando backups antiguos (más de 30 días)..."
find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +30 -delete
echo "✓ Limpieza completada"

# Listar backups recientes
echo ""
echo "Backups recientes:"
ls -lh "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | tail -5 || echo "  No hay backups"

echo ""
echo "=========================================="
echo "Backup completado exitosamente"
echo "=========================================="

