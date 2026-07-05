<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'prenom' => 'System',
            'email' => 'admin@medical.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'telephone' => '0123456789',
            'statut' => true,
        ]);

        User::create([
            'name' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'medecin1@medical.com',
            'password' => Hash::make('password'),
            'role' => 'medecin',
            'telephone' => '0123456790',
            'statut' => true,
        ]);

        User::create([
            'name' => 'Martin',
            'prenom' => 'Sophie',
            'email' => 'medecin2@medical.com',
            'password' => Hash::make('password'),
            'role' => 'medecin',
            'telephone' => '0123456791',
            'statut' => true,
        ]);

        User::create([
            'name' => 'Petit',
            'prenom' => 'Marie',
            'email' => 'reception@medical.com',
            'password' => Hash::make('password'),
            'role' => 'receptionniste',
            'telephone' => '0123456792',
            'statut' => true,
        ]);
    }
}
