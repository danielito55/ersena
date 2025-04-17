<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nombres_completos' => 'Administrador',
            'documento_identidad' => 'ADMIN001',
            'correo' => 'admin@sena.edu.co',
            'password' => Hash::make('admin123'),
            'rol' => 'admin',
        ]);
    }
}
