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
        'notes_partagees' => 'array',
        'documents_partagees' => 'array',
    ];

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
