## Docker: cómo levantar el aplicativo para pruebas

Este documento permite que otra persona pueda **probar el sistema sin instalar PHP/Composer/Node en su máquina**, usando Docker.

---

### Requisitos

-   **Docker Desktop** instalado y ejecutándose
-   **Docker Compose** (v2) habilitado

---

### 1) Clonar el repositorio

```bash
git clone <TU_REPO>
cd escaner-total
```

---

### 2) Levantar contenedores

En la raíz del proyecto (`escaner-total/`):

```bash
docker compose up -d --build
```

Esto levanta:

-   **app**: Laravel (Apache + PHP 8.1) en `http://localhost:8181`
-   **db**: MySQL 8 (puerto host `3307`)

---

### 3) Inicialización automática (APP_KEY, migraciones y seeders)

Al iniciar el contenedor `app`, el script `docker/entrypoint.sh` hace lo siguiente:

-   Si no existe `.env`, lo crea copiando `docker/env.example`
-   Espera a que **MySQL** esté listo
-   Genera `APP_KEY` si falta
-   Crea `public/storage` (`php artisan storage:link`) si no existe
-   Ejecuta migraciones si `RUN_MIGRATIONS=true`
-   Ejecuta seeders si `RUN_SEEDERS=true`

Estos flags están en `docker-compose.yml` y puedes cambiarlos:

-   `RUN_MIGRATIONS`: `"true"` o `"false"`
-   `RUN_SEEDERS`: `"true"` o `"false"`
-   `SEEDER_CLASS`: por defecto `Database\Seeders\DatabaseSeeder`

---

### 4) Acceso a la aplicación

-   **App**: `http://localhost:8181`
-   **Swagger** (si aplica): `http://localhost:8181/api/documentation`

---

### 5) Exportar CSV (Reportes)

La sección **Reportes** está disponible desde el menú lateral.
Al exportar:

-   el navegador descargará un archivo `.csv`
-   el archivo incluye BOM UTF-8 para abrirse bien en Excel

---

### 6) Comandos útiles

Ver logs:

```bash
docker compose logs -f app
docker compose logs -f db
```

Entrar al contenedor:

```bash
docker compose exec app bash
```

Ejecutar artisan:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan optimize:clear
```

Reiniciar servicios:

```bash
docker compose restart
```

Bajar contenedores:

```bash
docker compose down
```

⚠️ Bajar y borrar DB (reinicio total):

```bash
docker compose down -v
```

---

### 7) Variables importantes (Docker)

El template de entorno está en `docker/env.example`.

Base de datos por defecto:

-   DB_HOST: `db`
-   DB_DATABASE: `escaner_total`
-   DB_USERNAME: `escaner_user`
-   DB_PASSWORD: `root`

Raspberry/Device key (demo):

-   `DEVICE_KEY=abcd1234`

---

### 8) Solución de problemas

-   **Si el contenedor app no conecta a MySQL**: espera 10–30s y revisa logs: `docker compose logs -f db`.
-   **Si falta APP_KEY**: reinicia `app` (se genera automático) o ejecuta `docker compose exec app php artisan key:generate --force`.
-   **Permisos de storage**:

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```
