<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Consultation;

class PrestationsModifiees extends Notification
{
    use Queueable;

    protected $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $patient = $this->consultation->patient;
        $tarifs = $this->consultation->tarifs;
        $montant = $tarifs->sum('montant_ttc');
        $liste = $tarifs->pluck('acte')->implode(', ');
        return [
            'title' => 'Prestations modifiées - ' . number_format($montant, 2) . ' DT',
            'message' => 'Prestations mises à jour pour la consultation du ' . $this->consultation->date_consultation->format('d/m/Y') . ' de ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . ' : ' . $liste,
            'url' => route('consultations.show', $this->consultation),
            'icon' => 'bi-file-medical',
        ];
    }
}
