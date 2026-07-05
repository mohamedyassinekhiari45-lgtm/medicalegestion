<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'code_nomenclature', 'acte', 'montant_ht', 'tva', 'montant_ttc'
    ];

    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_tarif');
    }
}
