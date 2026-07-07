<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el usuario administrador del panel (si no existe).
     * Las credenciales se pueden personalizar con las variables de entorno
     * ADMIN_EMAIL / ADMIN_PASSWORD; si no, usa los valores por defecto.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'chelo@zooblog.com')],
            [
                'name'     => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
            ]
        );
    }
}
