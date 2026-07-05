<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierAuthorization extends Model
{
    protected $fillable = ['dossier_medical_id', 'medecin_id', 'autorise_par'];

    public function dossierMedical()
    {
        return $this->belongsTo(DossierMedical::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function autorisePar()
    {
        return $this->belongsTo(User::class, 'autorise_par');
    }
}
