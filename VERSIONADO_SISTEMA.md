# Versionado del Sistema — Ingreso Total (Escáner Total)

Este documento centraliza el **versionado/compatibilidad** del proyecto para despliegues y soporte.

> Fuente: `composer.json`, `composer.lock`, `package.json`, `Dockerfile` y `docker-compose.yml`.

---

## Backend (Laravel / PHP)

- **PHP (requerido)**: `^8.1` (según `composer.json`)
- **Laravel Framework (bloqueado)**: `v10.50.0` (según `composer.lock`)
- **Laravel Sanctum**: `^3.3` (según `composer.json`)
- **Inertia Laravel Adapter**: `^2.0` (según `composer.json`)
- **Ziggy (backend)**: `^2.6` (según `composer.json`)

### Paquetes clave (backend)

- **QR**: `simplesoftwareio/simple-qrcode` `4.2` (según `composer.json`)
- **PDF**: `barryvdh/laravel-dompdf` `^3.1` (según `composer.json`)
- **Swagger/OpenAPI**: `darkaonline/l5-swagger` `8.6` (según `composer.json`)
- **DBAL (migraciones/alter)**: `doctrine/dbal` `^3.10` (según `composer.json`)
- **HTTP Client**: `guzzlehttp/guzzle` `^7.2` (según `composer.json`)

---

## Frontend (Inertia + Vue + Tooling)

> El frontend ya está compilado en `public/build` para la imagen Docker de demo.

- **Vite**: `^5.0.0` (según `package.json`)
- **Vue**: `^3.5.26` (según `package.json`)
- **Inertia (Vue 3)**: `^2.3.4` (según `package.json`)
- **Tailwind CSS**: `^4.1.18` (según `package.json`)
- **PostCSS**: `^8.5.6` (según `package.json`)
- **laravel-vite-plugin**: `^1.0.0` (según `package.json`)
- **Ziggy (JS)**: `^2.6.0` (según `package.json`)
- **Axios**: `^1.6.4` (según `package.json`)

---

## Base de datos

- **Motor recomendado**: **MySQL 8.x**
- **Contenedor (Docker)**: `mysql:8.0` (según `docker-compose.yml`)

### Esquema (alto nivel)

Incluye módulos como:
- Usuarios / Roles / Cargos / Permisos
- Puertas / Pisos / Tipos de puerta / Materiales
- Accesos registrados (auditoría)
- QR (códigos, relaciones por puerta)
- Mantenimientos + defectos + evidencias (imágenes)
- Departamentos (tabla normalizada)

---

## Docker / Infra (para demo)

- **Imagen base app**: `php:8.1-apache-bookworm` (según `Dockerfile`)
- **Composer (binario)**: `composer:2` (según `Dockerfile`)
- **MySQL**: `mysql:8.0` (según `docker-compose.yml`)

### Puertos (por defecto)

- **Aplicación**: `http://localhost:8181` (mapeo `8181:80`)
- **MySQL**: `localhost:3307` (mapeo `3307:3306`)

---

## Variables de entorno (Docker)

Template: `docker/env.example`

Valores relevantes para demo:
- **APP_URL**: `http://localhost:8181`
- **DB_HOST**: `db`
- **DB_DATABASE**: `escaner_total`
- **DB_USERNAME**: `escaner_user`
- **DB_PASSWORD**: `root`
- **DEVICE_KEY (Raspberry/API)**: `abcd1234` (demo)

Flags de inicialización:
- **RUN_MIGRATIONS**: `"true"` / `"false"`
- **RUN_SEEDERS**: `"true"` / `"false"`
- **SEEDER_CLASS**: `Database\Seeders\DatabaseSeeder`

---

## Recomendación de compatibilidad (resumen)

- **PHP**: 8.1.x
- **MySQL**: 8.0.x
- **Laravel**: 10.50.x (según lock actual)
- **Frontend build**: Vite 5 + Vue 3 + Tailwind 4


