#!/bin/bash

# Script de deploy para producción
# Uso: ./scripts/deploy-production.sh

set -e

echo "=========================================="
echo "Deploy de Producción - Escáner Total"
echo "=========================================="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "docker-compose.prod.yml" ]; then
    echo "ERROR: No se encontró docker-compose.prod.yml"
    echo "Asegúrate de estar en el directorio raíz del proyecto"
    exit 1
fi

# Verificar que .env existe
if [ ! -f ".env" ]; then
    echo "ERROR: El archivo .env no existe"
    echo "Copia .env.production.example a .env y completa los valores"
    exit 1
fi

# Verificar que APP_ENV es production
if ! grep -q "APP_ENV=production" .env; then
    echo "ADVERTENCIA: APP_ENV no está configurado como 'production' en .env"
    read -p "¿Continuar de todas formas? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Paso 1: Obtener últimos cambios
echo "1. Obteniendo últimos cambios del repositorio..."
git fetch origin
git pull origin main || echo "ADVERTENCIA: No se pudo hacer pull"

# Paso 2: Backup de base de datos
echo ""
echo "2. Realizando backup de base de datos..."
if [ -f "scripts/backup-db.sh" ]; then
    bash scripts/backup-db.sh || echo "ADVERTENCIA: Backup falló, continuando..."
else
    echo "  Script de backup no encontrado, saltando..."
fi

# Paso 3: Reconstruir imágenes
echo ""
echo "3. Reconstruyendo imágenes Docker..."
docker compose -f docker-compose.prod.yml build --no-cache

# Paso 4: Detener contenedores
echo ""
echo "4. Deteniendo contenedores actuales..."
docker compose -f docker-compose.prod.yml down

# Paso 5: Levantar contenedores
echo ""
echo "5. Levantando contenedores actualizados..."
docker compose -f docker-compose.prod.yml up -d

# Paso 6: Esperar a que los servicios estén listos
echo ""
echo "6. Esperando a que los servicios estén listos..."
sleep 15

# Verificar salud de los contenedores
echo ""
echo "7. Verificando salud de los contenedores..."
if docker compose -f docker-compose.prod.yml ps | grep -q "Up"; then
    echo "✓ Contenedores levantados correctamente"
else
    echo "ERROR: Algunos contenedores no están corriendo"
    docker compose -f docker-compose.prod.yml ps
    exit 1
fi

# Paso 8: Ejecutar migraciones
echo ""
echo "8. Ejecutando migraciones..."
read -p "¿Ejecutar migraciones ahora? (Y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Nn]$ ]]; then
    docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
    echo "✓ Migraciones completadas"
else
    echo "  Migraciones omitidas. Ejecuta manualmente:"
    echo "  docker compose -f docker-compose.prod.yml exec app php artisan migrate --force"
fi

# Paso 9: Optimizar Laravel
echo ""
echo "9. Optimizando Laravel para producción..."
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec app php artisan optimize
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
echo "✓ Optimización completada"

# Paso 10: Verificar aplicación
echo ""
echo "10. Verificando que la aplicación responde..."
sleep 5
if curl -f -s http://localhost:8181 > /dev/null 2>&1; then
    echo "✓ La aplicación responde correctamente"
else
    echo "ADVERTENCIA: La aplicación no responde en http://localhost:8181"
    echo "  Verifica los logs: docker compose -f docker-compose.prod.yml logs app"
fi

# Resumen
echo ""
echo "=========================================="
echo "Deploy completado"
echo "=========================================="
echo ""
echo "Comandos útiles:"
echo "  Ver logs: docker compose -f docker-compose.prod.yml logs -f"
echo "  Estado: docker compose -f docker-compose.prod.yml ps"
echo "  Entrar al contenedor: docker compose -f docker-compose.prod.yml exec app bash"
echo ""

