<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Consultation;

class FactureImpayee extends Notification
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
        $montant = $this->consultation->tarifs->sum('montant_ttc');
        $patientNom = ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '');
        $montantTexte = $montant > 0 ? number_format($montant, 2) . ' DT' : '';

        return [
            'title' => 'Facture impayée' . ($montantTexte ? ' - ' . $montantTexte : ''),
            'message' => 'Aucune facture générée pour la consultation de ' . trim($patientNom) . ' du ' . $this->consultation->date_consultation->format('d/m/Y') . ($montantTexte ? ' (Montant : ' . $montantTexte . ')' : '') . '.',
            'url' => route('consultations.show', $this->consultation),
            'icon' => 'bi-exclamation-triangle',
        ];
    }
}
