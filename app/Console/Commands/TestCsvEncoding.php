<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCsvEncoding extends Command
{
    protected $signature = 'test:csv-encoding {--type=usuarios : Tipo de exportación (usuarios, accesos, mantenimientos, puertas)}';
    protected $description = 'Probar encoding UTF-8 con BOM en exports CSV';

    public function handle()
    {
        $type = $this->option('type');

        $this->info("🧪 Probando encoding CSV para: {$type}");
        $this->newLine();

        $testData = $this->generateTestData($type);

        if (!$testData) {
            $this->error("Tipo no válido. Usa: usuarios, accesos, mantenimientos, puertas");
            return 1;
        }

        // Generar CSV de prueba
        $filename = "test_encoding_{$type}_" . date('Y-m-d_His') . '.csv';
        $filepath = storage_path("app/public/{$filename}");

        $this->info("📝 Generando archivo CSV de prueba...");
        $this->generateCsv($filepath, $testData);

        // Verificar encoding
        $this->newLine();
        $this->info("🔍 Verificando encoding...");

        $this->verifyBOM($filepath);
        $this->verifyContent($filepath);
        $this->verifyFileEncoding($filepath);

        $this->newLine();
        $this->info("✅ Archivo generado en: {$filepath}");
        $this->info("📎 Abre el archivo en Excel para verificar visualmente que los caracteres especiales se ven correctamente.");

        return 0;
    }

    private function generateTestData(string $type): ?array
    {
        return match ($type) {
            'usuarios' => [
                'headers' => ['ID', 'Nombre', 'Email', 'Rol', 'Cargo', 'Departamento', 'Activo'],
                'rows' => [
                    [1, 'José Pérez', 'jose@test.com', 'Super Admin', 'Administrador', 'Recursos Humanos', 'Sí'],
                    [2, 'María González', 'maria@test.com', 'Operador', 'Operador', 'Seguridad', 'Sí'],
                    [3, 'Carlos Sánchez', 'carlos@test.com', 'RRHH', 'Gerente', 'Recursos Humanos', 'No'],
                ]
            ],
            'accesos' => [
                'headers' => ['ID', 'Fecha y Hora', 'Usuario', 'Puerta', 'Permitido'],
                'rows' => [
                    [1, '2025-01-15 10:30:00', 'José Pérez', 'Entrada Principal', 'Sí'],
                    [2, '2025-01-15 11:45:00', 'María González', 'Salida Lateral', 'Sí'],
                ]
            ],
            'mantenimientos' => [
                'headers' => ['ID', 'Fecha', 'Puerta', 'Usuario', 'Tipo', 'Observaciones'],
                'rows' => [
                    [1, '2025-01-10', 'Entrada Principal', 'José Pérez', 'Preventivo', 'Mantenimiento realizado correctamente. Se cambiaron aceites.'],
                    [2, '2025-01-12', 'Salida Lateral', 'María González', 'Correctivo', 'Se reparó el motor. Estado: Óptimo.'],
                ]
            ],
            'puertas' => [
                'headers' => ['ID', 'Nombre', 'Piso', 'Tipo', 'Material', 'Activo'],
                'rows' => [
                    [1, 'Entrada Principal', 'Piso 1', 'Abatible', 'Aluminio', 'Sí'],
                    [2, 'Salida Lateral', 'Piso 2', 'Corredera', 'Vidrio', 'Sí'],
                ]
            ],
            default => null
        };
    }

    private function generateCsv(string $filepath, array $data): void
    {
        $file = fopen($filepath, 'w');

        // BOM para UTF-8 (Excel compatibility)
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados
        fputcsv($file, $data['headers']);

        // Datos
        foreach ($data['rows'] as $row) {
            fputcsv($file, $row);
        }

        fclose($file);
    }

    private function verifyBOM(string $filepath): void
    {
        $handle = fopen($filepath, 'rb');
        $firstBytes = fread($handle, 3);
        fclose($handle);

        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

        if ($firstBytes === $bom) {
            $this->line("   ✓ BOM UTF-8 presente correctamente");
        } else {
            $this->error("   ✗ BOM UTF-8 NO encontrado");
            $this->line("      Bytes encontrados: " . bin2hex($firstBytes));
            $this->line("      Bytes esperados: " . bin2hex($bom));
        }
    }

    private function verifyContent(string $filepath): void
    {
        $content = file_get_contents($filepath);

        // Verificar caracteres especiales en español
        $specialChars = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Ñ', 'Á', 'É', 'Í', 'Ó', 'Ú'];
        $found = [];

        foreach ($specialChars as $char) {
            if (strpos($content, $char) !== false) {
                $found[] = $char;
            }
        }

        if (!empty($found)) {
            $this->line("   ✓ Caracteres especiales encontrados: " . implode(', ', $found));
        } else {
            $this->warn("   ⚠️  No se encontraron caracteres especiales en los datos de prueba");
        }

        // Verificar que no hay problemas de encoding
        if (mb_check_encoding($content, 'UTF-8')) {
            $this->line("   ✓ Contenido es UTF-8 válido");
        } else {
            $this->error("   ✗ El contenido NO es UTF-8 válido");
        }
    }

    private function verifyFileEncoding(string $filepath): void
    {
        $fileSize = filesize($filepath);
        $this->line("   ℹ️  Tamaño del archivo: {$fileSize} bytes");

        // El BOM agrega 3 bytes, así que el contenido debería ser > 3
        if ($fileSize > 3) {
            $this->line("   ✓ Archivo tiene contenido además del BOM");
        } else {
            $this->error("   ✗ Archivo parece estar vacío");
        }
    }
}
