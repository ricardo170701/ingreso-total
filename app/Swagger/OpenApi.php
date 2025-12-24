<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="Escaner Total API",
 *     version="1.0.0",
 *     description="Documentación de la API para control de accesos por QR"
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Servidor actual (mismo host/puerto donde está abierto Swagger UI)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Token en formato: Bearer {token}"
 * )
 */
class OpenApi
{
    // Este archivo existe solo para alojar metadatos OpenAPI a nivel global.
}
