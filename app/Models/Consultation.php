<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'rendez_vous_id', 'medecin_id', 'patient_id',
        'date_consultation', 'constantes', 'diagnostic',
        'observations', 'statut'
    ];

    protected $casts = [
        'date_consultation' => 'date',
        'constantes' => 'array',
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    public function tarifs()
    {
        return $this->belongsToMany(Tarif::class, 'consultation_tarif');
    }
}
