<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class NouveauRendezVous extends Notification
{
    use Queueable;

    protected $rendezVous;

    public function __construct(RendezVous $rendezVous)
    {
        $this->rendezVous = $rendezVous;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $patient = $this->rendezVous->patient;
        return [
            'title' => 'Nouveau rendez-vous à confirmer',
            'message' => 'Un rendez-vous a été planifié pour ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . ' le ' . $this->rendezVous->date_rdv->format('d/m/Y') . ' à ' . $this->rendezVous->heure_rdv . '. Veuillez confirmer votre disponibilité.',
            'url' => route('rendez-vous.show', $this->rendezVous),
            'icon' => 'bi-calendar-check',
        ];
    }
}
