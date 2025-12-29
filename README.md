# Ingreso Total (Escáner Total) — Control de accesos con QR (Laravel + Inertia)

Sistema para **generación de códigos QR** y **validación de accesos** por **puertas** en un edificio.

## Características principales (estado actual)

-   **Roles (bandera)**: solo `funcionario` y `visitante`.
-   **Permisos**: se gestionan por **Cargos** (no por rol).
-   **Visitante (restricción web)**: solo puede acceder a **Ingreso** (ver su QR) y **Soporte**.
-   **QR**:
    -   Expiración: **15 días**
    -   Se puede mostrar el **QR activo** (almacenando token cifrado en BD con `token_encrypted`)
    -   Si un usuario con permiso genera un nuevo QR, el anterior se desactiva.
-   **Puertas**:
    -   Listado con acciones rápidas (Ver / Abrir / Reiniciar) con permisos.
    -   “Hoja de vida” por puerta (detalle + acciones + mantenimientos).
-   **UPS** (módulo nuevo):
    -   CRUD de UPS con foto
    -   Mantenimientos con fotos + PDFs + ZIP + auditoría
-   **Swagger UI**: documentación OpenAPI en `/api/documentation`

> Nota: El “módulo” de **Mantenimientos** fue **deprecado en UI** (se quitó del menú), pero el backend/rutas se mantienen porque se usan desde Puertas y reportes.

---

## Versiones (referencia)

-   **PHP**: 8.1+
-   **Laravel**: 10.x
-   **Frontend**: Inertia + Vue + Vite

---

## Requisitos

### Desarrollo

-   PHP 8.1+
-   Composer 2.x
-   MySQL/MariaDB
-   Node.js 18+ (recomendado para assets Vite)

### Producción

-   Nginx/Apache + PHP-FPM 8.1+
-   Extensiones recomendadas: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `json`, `curl`, `fileinfo`, `ctype`
-   Para ZIP (mantenimientos UPS): extensión `zip` (`ZipArchive`) recomendada

---

## Instalación (desarrollo)

1. Instalar dependencias:

```bash
composer install
npm install
```

2. Configurar entorno:

```bash
copy .env.example .env
php artisan key:generate
```

3. Configurar base de datos en `.env` (ejemplo):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_accesos_qr
DB_USERNAME=root
DB_PASSWORD=TU_PASSWORD
```

4. Migraciones + seed:

```bash
php artisan migrate
php artisan db:seed
```

5. Archivos públicos (fotos/PDFs):

```bash
php artisan storage:link
```

6. Levantar servidores:

```bash
php artisan serve --host=127.0.0.1 --port=8001
npm run dev
```

Swagger UI:

-   `http://127.0.0.1:8001/api/documentation`

---

## Seeders (datos iniciales)

-   Crea permisos (`PermissionSeeder`) y estructura base (pisos/puertas, etc. según seeders).
-   Roles vigentes: **`funcionario`** y **`visitante`**.
-   El usuario admin queda como `funcionario` y normalmente se le asigna un cargo con todos los permisos (según `AccessControlSeeder`).

Variables opcionales (si aplica en tu seeder):

```env
SEED_ADMIN_EMAIL=admin@local.test
SEED_ADMIN_PASSWORD=admin12345
```

---

## Autorización (modelo mental)

-   **Rol**: solo define si el usuario es `funcionario` o `visitante`.
-   **Cargo**: define **todos los permisos** del sistema.
-   **Visitante**: aunque tenga cargo, en web queda restringido a:
    -   `/ingreso` (ver su QR)
    -   `/soporte`

---

## Uso (web)

-   **Dashboard**: indicadores + puertas (acciones rápidas con permisos).
-   **Puertas**:
    -   Index: Ver/Abrir/Reiniciar
    -   Show (hoja de vida): detalle + acciones + mantenimientos
-   **Ingreso**: QR personal (visitantes solo visualizan)
-   **UPS**: inventario + mantenimientos + adjuntos + ZIP

---

## API (resumen)

> La referencia completa está en Swagger: `/api/documentation`.

### Autenticación (Sanctum)

-   **Login**: `POST /api/auth/login`
-   **Logout** (revoca token actual): `POST /api/auth/logout` (requiere Bearer)

Ejemplo:

```bash
curl -X POST "http://127.0.0.1:8001/api/auth/login" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@local.test\",\"password\":\"admin12345\",\"device_name\":\"swagger\"}"
```

Respuesta:

```json
{
    "token_type": "Bearer",
    "token": "1|xxxx",
    "user": {
        "id": 1,
        "email": "admin@local.test",
        "role_id": 1,
        "cargo_id": 1
    }
}
```

### Generar QR (15 días)

-   **Endpoint**: `POST /api/qrs` (requiere Bearer)
-   **Body mínimo**:
    -   `user_id` (obligatorio)
-   **Reglas importantes**:
    -   Si el usuario destino es **visitante**, se debe enviar `pisos` (se expanden a puertas activas).
    -   Si generas para **otro usuario** y es **visitante**, se debe enviar `departamento_id` (departamento destino).
    -   Si no se envían `puertas`/`pisos`, el acceso se evaluará por **cargo** (para no visitantes).

Ejemplo (visitante con pisos):

```bash
curl -X POST "http://127.0.0.1:8001/api/qrs" ^
  -H "Authorization: Bearer 1|xxxx" ^
  -H "Content-Type: application/json" ^
  -d "{\"user_id\":10,\"pisos\":[1,2],\"departamento_id\":3}"
```

La respuesta incluye el `token` (valor a convertir a QR). En BD se guarda hash + `token_encrypted` para poder renderizar el QR activo.

### Validación de acceso (lector/Raspberry)

-   **Endpoint**: `POST /api/access/verify`
-   **Seguridad**: requiere header `X-DEVICE-KEY` y que `ACCESS_DEVICE_KEY` esté configurada en `.env` (fail-closed).
-   **Body**:
    -   `token` (valor leído del QR)
    -   `codigo_fisico` (identificador físico de la puerta/lector)
    -   `tipo_evento` (opcional): `entrada` / `salida` (por defecto `entrada`)
    -   `dispositivo_id` (opcional)

Ejemplo:

```bash
curl -X POST "http://127.0.0.1:8001/api/access/verify" ^
  -H "Content-Type: application/json" ^
  -H "X-DEVICE-KEY: TU_LLAVE" ^
  -d "{\"token\":\"TOKEN_QR\",\"codigo_fisico\":\"P1-ENT\",\"tipo_evento\":\"entrada\",\"dispositivo_id\":\"P1-ENT-RPI\"}"
```

Respuesta típica:

```json
{
    "permitido": true,
    "message": "Acceso permitido.",
    "data": {
        "user_id": 10,
        "puerta_id": 3,
        "codigo_qr_id": 99,
        "tipo_evento": "entrada",
        "dispositivo_id": "P1-ENT-RPI",
        "fecha": "2025-12-28T12:34:56+00:00"
    }
}
```

---

## Raspberry / lector (script `ingreso.py`)

El script `ingreso.py` llama a la API de verificación y activa relés si el backend responde `permitido=true`.

Variables típicas:

-   `API_BASE`
-   `CODIGO_FISICO`
-   `TIPO_EVENTO` (`entrada` / `salida`)
-   `DISPOSITIVO_ID`
-   `ACCESS_DEVICE_KEY` (si habilitas seguridad por llave)

---

## Notas

-   No se sube `vendor/`, `node_modules/` ni `.env`.
-   Documentación interna de módulos: `MODULOS_SISTEMA.md`.
