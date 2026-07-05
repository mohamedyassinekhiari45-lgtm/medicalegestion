<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Patient;

class NouveauPatient extends Notification
{
    use Queueable;

    protected $patient;

    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Nouveau patient',
            'message' => 'Le patient ' . ($this->patient->prenom ?? '') . ' ' . ($this->patient->nom ?? '') . ' a été ajouté.',
            'url' => route('patients.show', $this->patient),
            'icon' => 'bi-person-plus',
        ];
    }
}
