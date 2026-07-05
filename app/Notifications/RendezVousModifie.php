<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\RendezVous;

class RendezVousModifie extends Notification
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
            'title' => 'Rendez-vous modifié',
            'message' => 'Le rendez-vous du ' . $this->rendezVous->date_rdv->format('d/m/Y') . ' à ' . $this->rendezVous->heure_rdv . ' pour ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . ' a été modifié.',
            'url' => route('rendez-vous.show', $this->rendezVous),
            'icon' => 'bi-calendar-event',
        ];
    }
}
