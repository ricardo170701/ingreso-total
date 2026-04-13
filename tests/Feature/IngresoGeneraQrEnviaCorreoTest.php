<?php

namespace Tests\Feature;

use App\Mail\QrCodeMail;
use App\Models\Cargo;
use App\Models\Permission;
use App\Models\Piso;
use App\Models\Puerta;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IngresoGeneraQrEnviaCorreoTest extends TestCase
{
    use DatabaseTransactions;

    private function seedActor(): User
    {
        $roleStaff = Role::query()->firstOrCreate(
            ['name' => 'servidor_publico'],
            ['description' => 'Servidor público']
        );

        $cargo = Cargo::query()->firstOrCreate(
            ['name' => 'Operador Ingreso mail QR'],
            ['description' => 'Cargo para pruebas', 'activo' => true]
        );

        foreach (['create_ingreso', 'create_ingreso_otros'] as $name) {
            $perm = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['description' => null, 'group' => 'tests', 'activo' => true]
            );
            $cargo->permissions()->syncWithoutDetaching([$perm->id]);
        }

        return User::factory()->create([
            'email' => 'actor-mailqr+' . uniqid() . '@local.test',
            'password' => Hash::make('secret12345'),
            'password_changed_at' => now(),
            'activo' => true,
            'role_id' => $roleStaff->id,
            'cargo_id' => $cargo->id,
        ]);
    }

    public function test_al_generar_qr_para_visitante_con_correo_encola_correo(): void
    {
        Mail::fake();

        $roleVisitante = Role::query()->firstOrCreate(
            ['name' => 'visitante'],
            ['description' => 'Visitante']
        );

        $email = 'visitante-mailqr+' . uniqid() . '@local.test';
        $visitante = User::factory()->create([
            'email' => $email,
            'activo' => true,
            'role_id' => $roleVisitante->id,
            'cargo_id' => null,
        ]);

        $piso = Piso::query()->create([
            'nombre' => 'Piso mail QR',
            'orden' => 996,
            'activo' => true,
        ]);

        Puerta::query()->create([
            'nombre' => 'Puerta mail QR',
            'piso_id' => $piso->id,
            'activo' => true,
            'codigo_fisico' => 'MAIL-QR-' . uniqid(),
        ]);

        $actor = $this->seedActor();

        $res = $this->actingAs($actor)->post(route('ingreso.generate'), [
            'user_id' => $visitante->id,
            'pisos' => [$piso->id],
        ]);

        $res->assertOk();
        Mail::assertQueued(QrCodeMail::class, function (QrCodeMail $mail) use ($email) {
            return $mail->hasTo($email);
        });
    }
}
