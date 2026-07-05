<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Tarif;

class TarifSeeder extends Seeder
{
    public function run()
    {
        $tarifs = [
            ['code_nomenclature' => 'CS1', 'acte' => 'Consultation générale', 'montant_ht' => 30.00, 'tva' => 0],
            ['code_nomenclature' => 'CS2', 'acte' => 'Consultation spécialiste', 'montant_ht' => 50.00, 'tva' => 0],
            ['code_nomenclature' => 'VIS', 'acte' => 'Visite à domicile', 'montant_ht' => 60.00, 'tva' => 0],
            ['code_nomenclature' => 'ECG', 'acte' => 'Électrocardiogramme', 'montant_ht' => 40.00, 'tva' => 0],
            ['code_nomenclature' => 'PRISE', 'acte' => 'Prise de sang', 'montant_ht' => 15.00, 'tva' => 0],
            ['code_nomenclature' => 'RADIO', 'acte' => 'Radiographie', 'montant_ht' => 45.00, 'tva' => 0],
            ['code_nomenclature' => 'ECHO', 'acte' => 'Échographie', 'montant_ht' => 70.00, 'tva' => 0],
            ['code_nomenclature' => 'VAC', 'acte' => 'Vaccination', 'montant_ht' => 25.00, 'tva' => 0],
        ];

        foreach ($tarifs as $tarif) {
            $tarif['montant_ttc'] = $tarif['montant_ht'] * (1 + $tarif['tva'] / 100);
            Tarif::create($tarif);
        }
    }
}
