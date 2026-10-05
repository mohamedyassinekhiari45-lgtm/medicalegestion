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

    /**
     * Restreint la requete aux patients consultes par le medecin donne.
     * Un patient ayant au moins une consultation avec ce medecin.
     */
    public function scopeConsultesPar($query, $medecinId)
    {
        return $query->whereIn('id', function ($sub) use ($medecinId) {
            $sub->select('patient_id')
                ->from('consultations')
                ->where('medecin_id', $medecinId);
        });
    }

    /**
     * Indique si le medecin donne est le medecin traitant de ce patient,
     * c'est-a-dire s'il l'a consulte ou s'il a un rendez-vous avec lui.
     */
    public function estTraitePar($medecinId)
    {
        $medecinId = (int) $medecinId;
        if ($medecinId === 0) {
            return false;
        }

        if ($this->consultations()->where('medecin_id', $medecinId)->exists()) {
            return true;
        }

        return $this->rendezVous()->where('medecin_id', $medecinId)->exists();
    }

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
