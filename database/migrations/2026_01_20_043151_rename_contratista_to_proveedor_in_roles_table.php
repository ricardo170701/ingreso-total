<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        // Renombrar contratista a proveedor
        $contratista = DB::table('roles')->where('name', 'contratista')->first();
        if ($contratista) {
            DB::table('roles')->where('id', $contratista->id)->update([
                'name' => 'proveedor',
                'description' => 'Proveedor (mismas reglas que servidor público)',
                'updated_at' => now(),
            ]);
        } else {
            // Si no existe contratista, crear proveedor
            DB::table('roles')->updateOrInsert(
                ['name' => 'proveedor'],
                ['description' => 'Proveedor (mismas reglas que servidor público)', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        // Revertir proveedor a contratista
        $proveedor = DB::table('roles')->where('name', 'proveedor')->first();
        if ($proveedor) {
            DB::table('roles')->where('id', $proveedor->id)->update([
                'name' => 'contratista',
                'description' => 'Contratista (mismas reglas que servidor público)',
                'updated_at' => now(),
            ]);
        }
    }
};
