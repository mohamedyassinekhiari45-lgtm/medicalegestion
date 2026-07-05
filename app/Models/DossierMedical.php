<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierMedical extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'notes_generales', 'date_ouverture'];

    protected $casts = [
        'date_ouverture' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentMedical::class);
    }

    public function authorizations(): HasMany
    {
        return $this->hasMany(DossierAuthorization::class);
    }

    public function medecinsAutorises()
    {
        return $this->belongsToMany(User::class, 'dossier_authorizations', 'dossier_medical_id', 'medecin_id');
    }
}
