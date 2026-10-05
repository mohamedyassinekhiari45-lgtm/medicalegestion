<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierAuthorization extends Model
{
    protected $fillable = ['dossier_medical_id', 'medecin_id', 'autorise_par', 'partage_notes', 'partage_documents'];

    protected $casts = [
        'partage_notes' => 'boolean',
        'partage_documents' => 'boolean',
    ];

    /** Libelle lisible du perimetre accorde. */
    public function perimetreLibelle(): string
    {
        if ($this->partage_notes && $this->partage_documents) {
            return 'Notes et documents';
        }

        return $this->partage_notes ? 'Notes seules' : 'Documents seuls';
    }

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
