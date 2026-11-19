<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_admin_user_via_artisan_command_options(): void
    {
        $this->artisan('app:create-admin', [
            '--name' => 'Carlos Administrador',
            '--email' => 'admin@constructor.com',
            '--password' => 'PasswordSegura2026',
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('creado correctamente con rol \'administrador\'');

        $user = User::query()->where('email', 'admin@constructor.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Carlos Administrador', $user->name);
        $this->assertSame('administrador', $user->rol);
        $this->assertTrue(Hash::check('PasswordSegura2026', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_can_create_admin_user_via_environment_variables(): void
    {
        putenv('ADMIN_NAME=Admin Env');
        putenv('ADMIN_EMAIL=admin_env@constructor.com');
        putenv('ADMIN_PASSWORD=ClaveDeEntorno123');

        $this->artisan('app:create-admin')
            ->assertSuccessful()
            ->expectsOutputToContain('creado correctamente con rol \'administrador\'');

        $user = User::query()->where('email', 'admin_env@constructor.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Admin Env', $user->name);
        $this->assertSame('administrador', $user->rol);
        $this->assertTrue(Hash::check('ClaveDeEntorno123', $user->password));

        // Limpiar variables de entorno
        putenv('ADMIN_NAME');
        putenv('ADMIN_EMAIL');
        putenv('ADMIN_PASSWORD');
    }

    public function test_does_not_duplicate_existing_admin_user(): void
    {
        User::query()->create([
            'name' => 'Admin Existente',
            'email' => 'admin_existente@constructor.com',
            'password' => Hash::make('ClaveAnterior123'),
            'rol' => 'administrador',
        ]);

        $this->artisan('app:create-admin', [
            '--name' => 'Admin Otro',
            '--email' => 'admin_existente@constructor.com',
            '--password' => 'NuevaClave45678',
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('ya existe en el sistema');

        $this->assertSame(1, User::query()->where('email', 'admin_existente@constructor.com')->count());
        $user = User::query()->where('email', 'admin_existente@constructor.com')->first();
        $this->assertSame('Admin Existente', $user->name);
        $this->assertTrue(Hash::check('ClaveAnterior123', $user->password));
    }

    public function test_rejects_invalid_email_or_short_password(): void
    {
        $this->artisan('app:create-admin', [
            '--email' => 'correo-invalido',
            '--password' => 'corta',
        ])
            ->assertFailed();

        $this->assertSame(0, User::query()->count());
    }

    public function test_skips_cleanly_when_if_present_option_is_used_without_credentials(): void
    {
        $this->artisan('app:create-admin', ['--if-present' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Omitiendo creación de administrador');

        $this->assertSame(0, User::query()->count());
    }
}
