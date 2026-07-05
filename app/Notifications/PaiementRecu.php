<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Facture;
use App\Models\Paiement;

class PaiementRecu extends Notification
{
    use Queueable;

    protected $facture;
    protected $paiement;

    public function __construct(Facture $facture, Paiement $paiement)
    {
        $this->facture = $facture;
        $this->paiement = $paiement;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Paiement ' . number_format($this->paiement->montant, 2) . ' DT - Facture #' . $this->facture->numero_facture,
            'message' => 'Paiement de ' . number_format($this->paiement->montant, 2) . ' DT enregistré sur la facture #' . $this->facture->numero_facture . '.',
            'url' => route('factures.show', $this->facture),
            'icon' => 'bi-cash-stack',
        ];
    }
}
