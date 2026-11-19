<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin
                            {--name= : Nombre completo del administrador}
                            {--email= : Correo electrónico del administrador}
                            {--password= : Contraseña segura del administrador}
                            {--if-present : Solo ejecuta la creación si las credenciales están definidas, de lo contrario sale sin error}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea de forma segura el usuario administrador inicial si no existe en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?: env('ADMIN_EMAIL');
        $password = $this->option('password') ?: env('ADMIN_PASSWORD');
        $name = $this->option('name') ?: env('ADMIN_NAME');

        if ($this->option('if-present') && (empty($email) || empty($password))) {
            $this->info('Variables ADMIN_EMAIL o ADMIN_PASSWORD no configuradas. Omitiendo creación de administrador.');

            return Command::SUCCESS;
        }

        if (empty($email) && $this->input->isInteractive()) {
            $email = $this->ask('Ingresa el correo electrónico del administrador:');
        }

        if (empty($password) && $this->input->isInteractive()) {
            $password = $this->secret('Ingresa la contraseña del administrador (mínimo 8 caracteres):');
        }

        if (empty($name)) {
            $name = 'Administrador';
        }

        if (empty($email) || empty($password)) {
            $this->error('Error: Se requiere correo electrónico y contraseña.');
            $this->line('Configura las variables de entorno ADMIN_EMAIL y ADMIN_PASSWORD o pásalas como opciones:');
            $this->line('  php artisan app:create-admin --email=admin@ferreteria.com --password=tu_password_segura');

            return Command::FAILURE;
        }

        $email = strtolower(trim((string) $email));
        $name = trim((string) $name);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Error: El correo electrónico [{$email}] no es válido.");

            return Command::FAILURE;
        }

        if (strlen((string) $password) < 8) {
            $this->error('Error: La contraseña debe tener al menos 8 caracteres por seguridad.');

            return Command::FAILURE;
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null) {
            if ($existingUser->rol !== 'administrador') {
                $existingUser->update(['rol' => 'administrador']);
                $this->info("El usuario [{$email}] ya existía. Se actualizó su rol a 'administrador'.");

                return Command::SUCCESS;
            }

            $this->info("El usuario administrador [{$email}] ya existe en el sistema. No se realizaron cambios.");

            return Command::SUCCESS;
        }

        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make((string) $password),
            'rol' => 'administrador',
            'email_verified_at' => now(),
        ]);
        $user->save();

        $this->info("✓ Usuario administrador [{$email}] creado correctamente con rol 'administrador'.");

        return Command::SUCCESS;
    }
}
