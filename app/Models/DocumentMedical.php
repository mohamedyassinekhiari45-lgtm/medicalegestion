<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentMedical extends Model
{
    use HasFactory;

    protected $table = 'documents_medicaux';

    protected $fillable = ['dossier_medical_id', 'type', 'titre', 'description', 'fichier', 'uploaded_by'];

    public function dossierMedical(): BelongsTo
    {
        return $this->belongsTo(DossierMedical::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
