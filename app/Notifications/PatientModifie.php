<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Patient;

class PatientModifie extends Notification
{
    use Queueable;

    protected $patient;
    protected $action;

    public function __construct(Patient $patient, $action)
    {
        $this->patient = $patient;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $titre = $this->action === 'delete' ? 'Patient supprimé' : 'Patient modifié';
        $message = $this->action === 'delete'
            ? 'Le patient ' . ($this->patient->prenom ?? '') . ' ' . ($this->patient->nom ?? '') . ' a été supprimé.'
            : 'Le patient ' . ($this->patient->prenom ?? '') . ' ' . ($this->patient->nom ?? '') . ' a été modifié.';

        return [
            'title' => $titre,
            'message' => $message,
            'url' => route('patients.show', $this->patient),
            'icon' => 'bi-person',
        ];
    }
}
