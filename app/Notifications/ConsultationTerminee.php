<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Consultation;

class ConsultationTerminee extends Notification
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

        if ($notifiable->role === 'receptionniste') {
            return [
                'title' => 'Générer la facture' . ($montantTexte ? ' - ' . $montantTexte : ''),
                'message' => 'Veuillez générer la facture pour ' . trim($patientNom) . ' - Consultation du ' . $this->consultation->date_consultation->format('d/m/Y') . '.',
                'url' => route('factures.create', ['patient_id' => $this->consultation->patient_id]),
                'icon' => 'bi-receipt-cutoff',
            ];
        }

        return [
            'title' => 'Facture en cours de génération' . ($montantTexte ? ' - ' . $montantTexte : ''),
            'message' => 'Consultation de ' . trim($patientNom) . ' terminée le ' . $this->consultation->date_consultation->format('d/m/Y') . ($montantTexte ? ' (' . $montantTexte . ')' : '') . '. La facture est en attente.',
            'url' => route('consultations.show', $this->consultation),
            'icon' => 'bi-clipboard2-check',
        ];
    }
}
