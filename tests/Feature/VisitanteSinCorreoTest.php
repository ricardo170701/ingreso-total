<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Permission;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\TarjetaNfc;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VisitanteSinCorreoTest extends TestCase
{
    use DatabaseTransactions;

    private function seedPermisosParaActor(array $permissionNames): User
    {
        $roleStaff = Role::query()->firstOrCreate(
            ['name' => 'servidor_publico'],
            ['description' => 'Servidor público']
        );

        $cargo = Cargo::query()->firstOrCreate(
            ['name' => 'Operador Ingreso (tests)'],
            ['description' => 'Cargo para pruebas', 'activo' => true]
        );

        $permIds = [];
        foreach ($permissionNames as $name) {
            $perm = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['description' => null, 'group' => 'tests', 'activo' => true]
            );
            $permIds[] = $perm->id;
        }
        $cargo->permissions()->syncWithoutDetaching($permIds);

        return User::factory()->create([
            'email' => 'actor+' . uniqid() . '@local.test',
            'password' => Hash::make('secret12345'),
            // Pasar middleware force.password.change
            'password_changed_at' => now(),
            'activo' => true,
            'role_id' => $roleStaff->id,
            'cargo_id' => $cargo->id,
        ]);
    }

    private function makeVisitanteSinCorreo(): User
    {
        $roleVisitante = Role::query()->firstOrCreate(
            ['name' => 'visitante'],
            ['description' => 'Visitante']
        );

        return User::factory()->create([
            'email' => null,
            'activo' => true,
            'role_id' => $roleVisitante->id,
            'cargo_id' => null,
        ]);
    }

    public function test_no_permite_generar_qr_para_visitante_sin_correo(): void
    {
        // Middleware permission.check exige create_ingreso para la ruta ingreso.generate
        $actor = $this->seedPermisosParaActor(['create_ingreso', 'create_ingreso_otros']);
        $visitante = $this->makeVisitanteSinCorreo();

        $piso = Piso::query()->create([
            'nombre' => 'Piso Test QR',
            'orden' => 999,
            'activo' => true,
        ]);

        // Aunque enviemos pisos, debe fallar antes por falta de email
        $res = $this->actingAs($actor)->post(route('ingreso.generate'), [
            'user_id' => $visitante->id,
            'pisos' => [$piso->id],
        ]);

        $res->assertStatus(302);
        $res->assertSessionHasErrors(['user_id']);
    }

    public function test_permite_asignar_tarjeta_nfc_a_visitante_sin_correo(): void
    {
        $actor = $this->seedPermisosParaActor(['asignar_tarjetas_nfc']);
        $visitante = $this->makeVisitanteSinCorreo();

        $piso = Piso::query()->create([
            'nombre' => 'Piso Test NFC',
            'orden' => 998,
            'activo' => true,
        ]);

        $puerta = Puerta::query()->create([
            'nombre' => 'Puerta Test NFC',
            'piso_id' => $piso->id,
            'activo' => true,
            'codigo_fisico' => 'TEST-NFC-' . uniqid(),
        ]);

        $tarjeta = TarjetaNfc::query()->create([
            'codigo' => 'UID-' . uniqid(),
            'nombre' => 'Tarjeta Test',
            'user_id' => null,
            'activo' => true,
        ]);

        $res = $this->actingAs($actor)->postJson(route('ingreso.tarjetas-nfc.asignar'), [
            'tarjeta_nfc_id' => $tarjeta->id,
            'user_id' => $visitante->id,
            'gerencia_id' => null,
            'pisos' => [$piso->id],
            'hora_inicio' => null,
            'hora_fin' => null,
            'fecha_inicio' => null,
            'fecha_fin' => null,
        ]);

        $res->assertOk();
        $res->assertJsonFragment(['message' => 'Tarjeta NFC asignada correctamente.']);

        $this->assertDatabaseHas('tarjetas_nfc', [
            'id' => $tarjeta->id,
            'user_id' => $visitante->id,
        ]);

        $this->assertDatabaseHas('tarjeta_nfc_puerta_acceso', [
            'tarjeta_nfc_id' => $tarjeta->id,
            'puerta_id' => $puerta->id,
        ]);
    }
}

