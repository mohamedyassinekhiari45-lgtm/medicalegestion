<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id', 'medecin_id', 'cree_par', 'date_rdv',
        'heure_rdv', 'motif', 'statut', 'notes'
    ];

    protected $casts = [
        'date_rdv' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin()
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }
}
