<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {id} {--domain=} {--no-migrate}';
    protected $description = 'Crea un tenant (id=subdominio) y su dominio asociado. Ejecuta migraciones tenant por defecto.';

    public function handle(): int
    {
        $id = $this->argument('id');
        $domain = $this->option('domain') ?? ("{$id}.localhost");
        $runMigrations = ! $this->option('no-migrate');

        if (Tenant::find($id)) {
            $this->error("El tenant '{$id}' ya existe.");
            return Command::FAILURE;
        }

        // Crear tenant (los managers crearán la BD: prefix + id)
        $tenant = Tenant::create([
            'id' => $id,
            // Puedes agregar data extra: 'data' => ['plan' => 'basic']
        ]);

        // Crear dominio asociado
        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
        ]);

        $this->info("Tenant '{$id}' creado con dominio '{$domain}'.");

        if ($runMigrations) {
            $this->call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ]);

            // Crear usuario admin por defecto
            $this->createDefaultAdmin($tenant);
        } else {
            $this->line('Omitiendo migraciones (--no-migrate).');
        }

        $this->info('Listo.');
        return Command::SUCCESS;
    }

    /**
     * Crea el usuario admin por defecto para el tenant
     */
    private function createDefaultAdmin(Tenant $tenant): void
    {
        // Inicializar contexto del tenant para usar su base de datos
        tenancy()->initialize($tenant);

        $adminEmail = "admin@{$tenant->id}.localhost";

        \App\Models\Usuario::create([
            'nombre' => 'Administrador',
            'email' => $adminEmail,
            'password' => Hash::make('segundoparcial'),
            'rol' => 'admin',
            'must_change_password' => true,
        ]);

        $this->info("✓ Usuario admin creado (email: '{$adminEmail}', password: 'segundoparcial')");
        $this->warn("⚠ El admin debe cambiar la contraseña en su primer login");
    }
}
