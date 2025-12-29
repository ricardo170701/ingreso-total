# Configuración de Nginx

Este directorio contiene la configuración de Nginx como reverse proxy para la aplicación en producción.

## Estructura

```
nginx/
├── nginx.conf          # Configuración principal de Nginx
├── ssl/                # Certificados SSL (crear manualmente)
│   ├── fullchain.pem
│   └── privkey.pem
├── logs/               # Logs de Nginx (se crea automáticamente)
└── conf.d/             # Configuraciones adicionales (opcional)
```

## Configuración

### 1. Editar dominio

Edita `nginx.conf` y reemplaza `tu-dominio.com` por tu dominio real:

```nginx
server_name tu-dominio.com www.tu-dominio.com;
```

### 2. Certificados SSL

Coloca tus certificados SSL en `nginx/ssl/`:

-   `fullchain.pem` - Certificado completo
-   `privkey.pem` - Clave privada

#### Obtener certificados con Let's Encrypt:

```bash
# Instalar certbot
sudo apt-get install certbot

# Obtener certificado
sudo certbot certonly --standalone -d tu-dominio.com -d www.tu-dominio.com

# Copiar certificados
sudo cp /etc/letsencrypt/live/tu-dominio.com/fullchain.pem nginx/ssl/
sudo cp /etc/letsencrypt/live/tu-dominio.com/privkey.pem nginx/ssl/
sudo chmod 644 nginx/ssl/*.pem
```

### 3. Crear directorios

```bash
mkdir -p nginx/ssl nginx/logs nginx/conf.d
```

## Características

-   ✅ Redirección HTTP a HTTPS
-   ✅ Headers de seguridad
-   ✅ Rate limiting para API y login
-   ✅ Compresión gzip
-   ✅ Cache de assets estáticos
-   ✅ Protección de archivos sensibles
-   ✅ Proxy a aplicación Laravel

## Renovación de certificados (Let's Encrypt)

Agrega a crontab para renovación automática:

```bash
0 0 1 */3 * certbot renew --quiet && docker compose -f docker-compose.prod.yml restart nginx
```

## Troubleshooting

### Ver logs

```bash
docker compose -f docker-compose.prod.yml logs nginx
tail -f nginx/logs/app_error.log
```

### Probar configuración

```bash
docker compose -f docker-compose.prod.yml exec nginx nginx -t
```

### Reiniciar Nginx

```bash
docker compose -f docker-compose.prod.yml restart nginx
```
