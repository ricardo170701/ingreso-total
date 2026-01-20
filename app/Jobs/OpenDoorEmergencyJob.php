<?php

namespace App\Jobs;

use App\Models\ProtocolRunItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenDoorEmergencyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2; // Reintentar 2 veces
    public int $timeout = 5; // Timeout de 5 segundos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $protocolRunItemId,
        public string $ip,
        public int $durationSeconds = 900
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $item = ProtocolRunItem::query()->with('puerta')->findOrFail($this->protocolRunItemId);

        // Actualizar estado a "enviado"
        $item->update([
            'estado' => 'enviado',
            'enviado_at' => now(),
            'intentos' => $item->intentos + 1,
        ]);

        $apiKey = env('DOOR_API_KEY', 'D8738A38CC8FC927C5EC594F47A22787');
        $port = config('app.door_emergency_port', env('DOOR_EMERGENCY_PORT', 8000));
        $url = "http://{$this->ip}:{$port}/api/emergency/activate";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'duration_seconds' => $this->durationSeconds,
                ]);

            $httpStatus = $response->status();
            $body = $response->json();

            if ($httpStatus === 200 && isset($body['ok']) && $body['ok'] === true) {
                // Éxito
                $item->update([
                    'estado' => 'exitoso',
                    'http_status' => $httpStatus,
                    'respuesta' => json_encode($body),
                    'completado_at' => now(),
                ]);
            } else {
                // Error en la respuesta
                $item->update([
                    'estado' => 'fallido',
                    'http_status' => $httpStatus,
                    'respuesta' => json_encode($body),
                    'error' => 'Respuesta inválida del servidor',
                    'completado_at' => now(),
                ]);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Timeout o conexión fallida
            $item->update([
                'estado' => 'timeout',
                'error' => 'Timeout o conexión fallida: ' . $e->getMessage(),
                'completado_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Otro error
            $item->update([
                'estado' => 'fallido',
                'error' => $e->getMessage(),
                'completado_at' => now(),
            ]);
        }
    }
}
