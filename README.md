# Ingreso Total (Escáner Total) — API de Control de Accesos con QR (Laravel)

Sistema para **generación de QR temporales** y **validación de accesos** por puertas/zonas en un edificio, con:

-   **Roles** (permisos del sistema): `super_usuario`, `operador`, `rrhh`, `funcionario`, `visitante`
-   **Cargos** (permisos físicos): acceso a puertas por tabla pivote `cargo_puerta_acceso`
-   **Puertas especiales**: puertas que requieren discapacidad (`requiere_discapacidad`)
-   **QR temporal (24h)**: token opaco, almacenado como **hash sha256** en BD
-   **Entrada/Salida**: no permite una nueva **entrada** hasta que exista una **salida**
-   **Auditoría**: registros en `accesos_registrados` con `dispositivo_id` (Raspberry/lector)
-   **Swagger UI**: documentación OpenAPI en `/api/documentation`

---

## Versiones actuales

### Backend

-   **PHP**: `^8.1` (requerido por `composer.json`)
-   **Laravel Framework**: `v10.50.0` (según `composer.lock`)
-   **Laravel Sanctum**: `v3.3.3` (según `composer.lock`)
-   **L5 Swagger**: `8.6.0` (según `composer.lock`)
-   **swagger-php**: `4.11.1` (según `composer.lock`)
-   **Doctrine DBAL**: `3.10.4` (según `composer.lock`)

### Frontend tooling

-   **Vite**: `^5.0.0` (según `package.json`)
-   **laravel-vite-plugin**: `^1.0.0`
-   **axios**: `^1.6.4`

---

## Requisitos

### Para desarrollo

-   PHP 8.1+
-   Composer 2.x
-   MySQL/MariaDB
-   (Opcional) Node.js 18+ para assets con Vite

### Para producción (servidor)

-   Nginx o Apache
-   PHP-FPM 8.1+ con extensiones recomendadas:
    -   `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `json`, `curl`, `fileinfo`, `ctype`
-   MySQL/MariaDB

---

## Instalación (desarrollo)

1. Clonar e instalar dependencias:

```bash
git clone https://github.com/ricardo170701/ingreso-total.git
cd ingreso-total
composer install
```

2. Configurar entorno:

```bash
copy .env.example .env
php artisan key:generate
```

3. Configura la BD en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_accesos_qr
DB_USERNAME=root
DB_PASSWORD=TU_PASSWORD
```

4. Migrar y seedear:

```bash
php artisan migrate
php artisan db:seed
```

5. Levantar servidor:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

6. Swagger UI:

-   `http://127.0.0.1:8001/api/documentation`

---

## Seeders (datos iniciales)

El seeder crea:

-   Roles base
-   Cargos demo
-   Zonas/Puertas demo
-   Usuarios demo (y super usuario)

### Super usuario inicial (por defecto)

-   Email: `admin@local.test`
-   Password: `admin12345`

Puedes personalizarlo en `.env`:

```env
SEED_ADMIN_EMAIL=tuadmin@tudominio.com
SEED_ADMIN_PASSWORD=TuClaveSegura123
```

---

## Instalación en servidor (producción)

### 1) Subir código

Clona el repo en tu servidor (ejemplo: `/var/www/ingreso-total`):

```bash
git clone https://github.com/ricardo170701/ingreso-total.git
cd ingreso-total
composer install --no-dev --optimize-autoloader
```

### 2) Configurar `.env`

Crea/edita `.env` con:

-   `APP_ENV=production`
-   `APP_DEBUG=false`
-   `APP_URL=https://tudominio.com`
-   credenciales de BD

Luego:

```bash
php artisan key:generate
```

### 3) Permisos de carpetas

Asegura permisos de escritura:

-   `storage/`
-   `bootstrap/cache/`

(En Linux, normalmente con el usuario del servidor web, ej. `www-data`).

### 4) Migraciones y seed (solo la primera vez)

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5) Optimización

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6) Nginx (ejemplo)

Root debe apuntar a `public/`.

### 7) Swagger en producción

Swagger queda en:

-   `/api/documentation`

Si quieres restringirlo, puedes configurar middleware en `config/l5-swagger.php`.

---

## Uso rápido de la API

### Obtener token (login)

`POST /api/auth/login`

Body:

```json
{
    "email": "admin@local.test",
    "password": "admin12345",
    "device_name": "swagger"
}
```

Respuesta: `token` tipo Bearer.

### Crear usuario

`POST /api/users` (requiere Bearer token)

### Generar QR temporal (24h)

`POST /api/qrs` (requiere Bearer token)

-   Para **visitante**, `puertas` es obligatorio.
-   Para otros roles, `puertas` es opcional y el acceso se valida por **cargo**.

### Verificar acceso desde lector/Raspberry

`POST /api/access/verify` (endpoint para el lector)

Body:

```json
{
    "token": "TOKEN_LEIDO_DEL_QR",
    "codigo_fisico": "P1-ENT",
    "tipo_evento": "entrada",
    "dispositivo_id": "P1-ENT-RPI-ENTRADA"
}
```

---

## Raspberry / lector (script `ingreso.py`)

El script `ingreso.py` llama a la API de verificación y abre relés si el backend responde `permitido=true`.

Variables de entorno recomendadas por Raspberry:

-   `API_BASE`: URL del backend (ej. `http://IP_SERVIDOR:8001`)
-   `CODIGO_FISICO`: puerta (ej. `P1-ENT`)
-   `TIPO_EVENTO`: `entrada` o `salida`
-   `DISPOSITIVO_ID`: identificador único del dispositivo
-   `DEVICE_KEY` (opcional): si activas seguridad por llave
-   `OPEN_SECONDS` (opcional): segundos que activa el relé (default 5)

---

## Seguridad opcional del endpoint del lector

Si defines en `.env`:

```env
ACCESS_DEVICE_KEY=TU_LLAVE
```

El lector debe enviar header:

-   `X-DEVICE-KEY: TU_LLAVE`

---

## Notas

-   No se sube `vendor/`, `node_modules/`, `.env` ni `storage/logs|framework|api-docs` al repositorio.
