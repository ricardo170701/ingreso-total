# Scripts de Producción

Este directorio contiene scripts útiles para el despliegue y mantenimiento en producción.

## Scripts Disponibles

### `backup-db.sh`

Realiza un backup de la base de datos y lo comprime.

**Uso:**

```bash
chmod +x scripts/backup-db.sh
./scripts/backup-db.sh
```

**Características:**

-   Crea backup con timestamp
-   Comprime el backup automáticamente
-   Limpia backups antiguos (más de 30 días)
-   Guarda backups en `./backups/`

**Configuración:**
Asegúrate de tener las variables de entorno configuradas:

-   `DB_DATABASE`
-   `DB_USERNAME`
-   `DB_PASSWORD`

### `deploy-production.sh`

Script completo de deploy para producción.

**Uso:**

```bash
chmod +x scripts/deploy-production.sh
./scripts/deploy-production.sh
```

**Proceso:**

1. Obtiene últimos cambios del repositorio
2. Realiza backup de base de datos
3. Reconstruye imágenes Docker
4. Reinicia contenedores
5. Ejecuta migraciones (opcional)
6. Optimiza Laravel
7. Verifica que la aplicación responde

**Requisitos:**

-   Archivo `.env` configurado
-   `docker-compose.prod.yml` presente
-   Acceso al repositorio Git

## Configuración de Cron para Backups Automáticos

Para realizar backups automáticos diarios, agrega a tu crontab:

```bash
# Backup diario a las 2:00 AM
0 2 * * * cd /ruta/al/proyecto && ./scripts/backup-db.sh >> /var/log/backup-db.log 2>&1
```

O edita el crontab:

```bash
crontab -e
```

## Notas de Seguridad

-   Los scripts requieren permisos de ejecución: `chmod +x scripts/*.sh`
-   Asegúrate de que el archivo `.env` no esté en el repositorio
-   Los backups contienen información sensible, protégelos adecuadamente
-   Considera encriptar los backups antes de almacenarlos
