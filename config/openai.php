<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Referencia de costos (texto) — bitácora UPS / visión
    |--------------------------------------------------------------------------
    |
    | Precios orientativos por 1 millón de tokens (USD), según documentación
    | pública de OpenAI (pueden cambiar; ver https://openai.com/api/pricing).
    | Las imágenes suman tokens de entrada extra según tamaño y "detail".
    |
    | +------------------+-------------+--------------+
    | | Modelo           | Entrada /1M | Salida /1M   |
    | +------------------+-------------+--------------+
    | | gpt-4o-mini      | ~$0.15      | ~$0.60       |
    | | gpt-4.1          | ~$2.00      | ~$8.00       |
    | | gpt-4o           | ~$2.50      | ~$10.00      |
    | +------------------+-------------+--------------+
    |
    | Orden aproximado de costo por la misma foto + prompt: mini << 4.1 < 4o.
    | Con detail=high las imágenes consumen más tokens de entrada que con low.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => (int) env('OPENAI_REQUEST_TIMEOUT', 90),

    /*
    |--------------------------------------------------------------------------
    | Bitácora UPS (visión)
    |--------------------------------------------------------------------------
    */

    'ups_bitacora_ai_enabled' => env('UPS_BITACORA_AI_ENABLED', true),

    /** Modelo multimodal: gpt-4o-mini | gpt-4o | gpt-4.1 (o snapshot documentado). */
    'ups_bitacora_model' => env('UPS_BITACORA_OPENAI_MODEL', 'gpt-4o'),

    /**
     * Calidad de análisis de imagen en la API (auto|low|high).
     * "high" mejora lectura de LCD pixelados y tablas A/B/C; aumenta tokens de entrada.
     */
    'ups_bitacora_image_detail' => env('UPS_BITACORA_OPENAI_IMAGE_DETAIL', 'high'),

    /** Máximo de tokens de salida para el JSON de lectura (varias fases + alarmas). */
    'ups_bitacora_max_output_tokens' => (int) env('UPS_BITACORA_OPENAI_MAX_OUTPUT_TOKENS', 2000),

    /** Forzar salida JSON del modelo (response_format json_object); desactivar si la API devuelve error con tu modelo. */
    'ups_bitacora_json_object_mode' => filter_var(env('UPS_BITACORA_OPENAI_JSON_OBJECT', true), FILTER_VALIDATE_BOOL),

    /** Tolerancia porcentual al comparar lecturas del formulario vs JSON inferido por IA (0 = desactiva comparación estricta). */
    'ups_bitacora_compare_tolerance_pct' => env('UPS_BITACORA_COMPARE_TOLERANCE_PCT', 8),
];
