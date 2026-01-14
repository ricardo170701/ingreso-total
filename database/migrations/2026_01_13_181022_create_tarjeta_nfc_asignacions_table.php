<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // IMPORTANTE (MySQL): los nombres de índices tienen límite de 64 caracteres.
        // Usamos nombres cortos explícitos para evitar "Identifier name is too long".
        $idxCardFa = 'tnfc_asig_card_fa_idx';
        $idxUserFa = 'tnfc_asig_user_fa_idx';
        $idxCardFd = 'tnfc_asig_card_fd_idx';

        if (!Schema::hasTable('tarjeta_nfc_asignaciones')) {
            Schema::create('tarjeta_nfc_asignaciones', function (Blueprint $table) use ($idxCardFa, $idxUserFa, $idxCardFd) {
                $table->id();
                $table->foreignId('tarjeta_nfc_id')->constrained('tarjetas_nfc')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('gerencia_id')->nullable()->constrained('gerencias')->nullOnDelete();

                $table->timestamp('fecha_asignacion')->useCurrent();
                $table->timestamp('fecha_desasignacion')->nullable();

                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->index(['tarjeta_nfc_id', 'fecha_asignacion'], $idxCardFa);
                $table->index(['user_id', 'fecha_asignacion'], $idxUserFa);
                $table->index(['tarjeta_nfc_id', 'fecha_desasignacion'], $idxCardFd);
            });
            return;
        }

        // Si el migrate falló a mitad (tabla existe pero faltan índices), completarlos.
        $existing = collect(DB::select("SHOW INDEX FROM tarjeta_nfc_asignaciones"))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();

        Schema::table('tarjeta_nfc_asignaciones', function (Blueprint $table) use ($existing, $idxCardFa, $idxUserFa, $idxCardFd) {
            if (!in_array($idxCardFa, $existing, true)) {
                $table->index(['tarjeta_nfc_id', 'fecha_asignacion'], $idxCardFa);
            }
            if (!in_array($idxUserFa, $existing, true)) {
                $table->index(['user_id', 'fecha_asignacion'], $idxUserFa);
            }
            if (!in_array($idxCardFd, $existing, true)) {
                $table->index(['tarjeta_nfc_id', 'fecha_desasignacion'], $idxCardFd);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjeta_nfc_asignaciones');
    }
};
