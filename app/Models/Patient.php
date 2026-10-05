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
