<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Facture;

class FactureGeneree extends Notification
{
    use Queueable;

    protected $facture;

    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Facture #' . $this->facture->numero_facture . ' générée - ' . number_format($this->facture->montant_total, 2) . ' DT',
            'message' => 'Facture #' . $this->facture->numero_facture . ' : ' . number_format($this->facture->montant_total, 2) . ' DT générée.',
            'url' => route('factures.show', $this->facture),
            'icon' => 'bi-receipt',
        ];
    }
}
