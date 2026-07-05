<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'consultation_id', 'genere_par', 'numero_facture',
        'montant_total', 'montant_paye', 'statut_paiement'
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function generateur()
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
