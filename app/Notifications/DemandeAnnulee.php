<?php

namespace App\Notifications;

use App\Models\ShareRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DemandeAnnulee extends Notification
{
    use Queueable;

    protected $shareRequest;

    public function __construct(ShareRequest $shareRequest)
    {
        $this->shareRequest = $shareRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $patient = $this->shareRequest->dossierMedical->patient;

        $message = 'Dr ' . ($this->shareRequest->requester->prenom ?? '') . ' ' . ($this->shareRequest->requester->name ?? '')
            . ' a annulé sa demande d\'accès à ' . mb_strtolower($this->shareRequest->perimetreLibelle())
            . ' dans votre section du dossier de ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . '.';

        return [
            'title' => 'Demande d\'accès annulée',
            'message' => $message,
            'url' => route('dossiers-medicaux.show', $patient),
            'icon' => 'bi-x-circle',
            'share_request_id' => $this->shareRequest->id,
        ];
    }
}