<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Specialite;

class SpecialiteSeeder extends Seeder
{
    public function run()
    {
        $specialites = [
            ['libelle' => 'Médecine générale', 'description' => 'Médecine générale et soins primaires'],
            ['libelle' => 'Pédiatrie', 'description' => 'Soins médicaux pour enfants'],
            ['libelle' => 'Cardiologie', 'description' => 'Maladies du cœur et du système cardiovasculaire'],
            ['libelle' => 'Dermatologie', 'description' => 'Maladies de la peau'],
            ['libelle' => 'Gynécologie', 'description' => 'Santé de la femme'],
            ['libelle' => 'Ophtalmologie', 'description' => 'Soins des yeux et de la vision'],
            ['libelle' => 'ORL', 'description' => 'Oto-rhino-laryngologie'],
            ['libelle' => 'Neurologie', 'description' => 'Maladies du système nerveux'],
        ];

        foreach ($specialites as $spec) {
            Specialite::create($spec);
        }
    }
}
