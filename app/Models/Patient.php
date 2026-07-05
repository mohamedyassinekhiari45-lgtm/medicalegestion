<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_dossier', 'nom', 'prenom', 'email', 'telephone',
        'adresse', 'date_naissance', 'sexe'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function dossierMedical()
    {
        return $this->hasOne(DossierMedical::class);
    }
}
