<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareRequest extends Model
{
    protected $fillable = [
        'dossier_medical_id', 'from_medecin_id', 'to_medecin_id', 'requester_id',
        'notes_partagees', 'documents_partagees', 'statut',
    ];

    protected $casts = [
        'notes_partagees' => 'boolean',
        'documents_partagees' => 'boolean',
    ];

    /**
     * Les colonnes sont des JSON : avant le perimetre selectif elles contenaient
     * une liste d'identifiants, et non un booleen. On interprete donc les
     * anciennes valeurs comme un perimetre complet (notes + documents).
     */
    public function getNotesPartageesAttribute($value)
    {
        return $this->perimetreBooléen($value);
    }

    public function getDocumentsPartageesAttribute($value)
    {
        return $this->perimetreBooléen($value);
    }

    protected function perimetreBooléen($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return false;
        }

        // Valeurs historiques : liste JSON d'identifiants. Avant le perimetre
        // selectif, une demande portait sur toute la section du proprietaire.
        if (is_array($value)) {
            return true;
        }
        if (is_string($value)) {
            $decode = json_decode($value, true);
            if (is_array($decode)) {
                return true;
            }
        }

        $value = strtolower(trim((string)$value));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public function perimetreLibelle(): string
    {
        if ($this->notes_partagees && $this->documents_partagees) {
            return 'Notes et documents';
        }

        return $this->notes_partagees ? 'Notes' : 'Documents';
    }

    public function dossierMedical()
    {
        return $this->belongsTo(DossierMedical::class);
    }

    public function fromMedecin()
    {
        return $this->belongsTo(User::class, 'from_medecin_id');
    }

    public function toMedecin()
    {
        return $this->belongsTo(User::class, 'to_medecin_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
