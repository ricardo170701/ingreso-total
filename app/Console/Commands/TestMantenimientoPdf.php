<?php

namespace App\Console\Commands;

use App\Models\Mantenimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestMantenimientoPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mantenimiento-pdf {--id= : ID del mantenimiento a probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar generación de PDF de mantenimientos en diferentes escenarios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mantenimientoId = $this->option('id');

        if ($mantenimientoId) {
            $mantenimiento = Mantenimiento::with(['puerta.piso', 'usuario', 'defectos', 'imagenes'])
                ->find($mantenimientoId);

            if (!$mantenimiento) {
                $this->error("Mantenimiento #{$mantenimientoId} no encontrado.");
                return 1;
            }

            $this->testPdf($mantenimiento);
        } else {
            // Probar todos los mantenimientos
            $mantenimientos = Mantenimiento::with(['puerta.piso', 'usuario', 'defectos', 'imagenes'])->get();

            if ($mantenimientos->isEmpty()) {
                $this->warn('No hay mantenimientos en la base de datos.');
                return 0;
            }

            $this->info("Probando PDF con {$mantenimientos->count()} mantenimiento(s)...\n");

            foreach ($mantenimientos as $mantenimiento) {
                $this->testPdf($mantenimiento);
            }
        }

        return 0;
    }

    private function testPdf(Mantenimiento $mantenimiento): void
    {
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Probando Mantenimiento #{$mantenimiento->id}");

        // Mostrar información del mantenimiento
        $this->line("  Puerta: " . ($mantenimiento->puerta->nombre ?? 'N/A'));
        $this->line("  Piso: " . ($mantenimiento->puerta->piso->nombre ?? 'N/A'));
        $this->line("  Usuario: " . ($mantenimiento->usuario->name ?? $mantenimiento->usuario->email ?? 'N/A'));
        $this->line("  Defectos: " . $mantenimiento->defectos->count());
        $this->line("  Imágenes: " . $mantenimiento->imagenes->count());
        $this->line("  Tipo: " . ($mantenimiento->tipo ?? 'N/A'));
        $this->line("  Fecha: " . ($mantenimiento->fecha_mantenimiento ? $mantenimiento->fecha_mantenimiento->format('d/m/Y') : 'N/A'));
        $this->line("  Otros defectos: " . ($mantenimiento->otros_defectos ? 'Sí' : 'No'));
        $this->line("  Observaciones: " . ($mantenimiento->observaciones ? 'Sí' : 'No'));

        // Verificar casos edge
        $warnings = [];
        if (!$mantenimiento->puerta) {
            $warnings[] = "⚠️  Sin puerta";
        }
        if (!$mantenimiento->usuario) {
            $warnings[] = "⚠️  Sin usuario";
        }
        if ($mantenimiento->defectos->count() === 0) {
            $warnings[] = "⚠️  Sin defectos (caso edge)";
        }
        if ($mantenimiento->imagenes->count() === 0) {
            $warnings[] = "⚠️  Sin imágenes (caso edge)";
        }
        if (!$mantenimiento->fecha_mantenimiento) {
            $warnings[] = "⚠️  Sin fecha (caso edge)";
        }

        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                $this->warn("  {$warning}");
            }
        }

        try {
            // Intentar generar el PDF
            $pdf = Pdf::loadView('mantenimientos.pdf', [
                'mantenimiento' => $mantenimiento,
            ]);

            // Guardar temporalmente para verificar
            $tempPath = storage_path('app/temp_test_pdf_' . $mantenimiento->id . '.pdf');
            $pdf->save($tempPath);

            if (file_exists($tempPath)) {
                $size = filesize($tempPath);
                $this->info("  ✅ PDF generado exitosamente ({$size} bytes)");

                // Limpiar archivo temporal
                unlink($tempPath);
            } else {
                $this->error("  ❌ Error: El PDF no se generó correctamente");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Error al generar PDF: " . $e->getMessage());
            $this->line("  Archivo: " . $e->getFile());
            $this->line("  Línea: " . $e->getLine());
        }

        $this->line("");
    }
}
