<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'facture_id', 'encaisse_par', 'montant',
        'mode_reglement', 'reference', 'date_paiement'
    ];

    protected $casts = [
        'date_paiement' => 'date',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function encaisseur()
    {
        return $this->belongsTo(User::class, 'encaisse_par');
    }
}
