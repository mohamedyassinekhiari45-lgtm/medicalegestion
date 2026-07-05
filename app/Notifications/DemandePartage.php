<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ShareRequest;

class DemandePartage extends Notification
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
        $ownerInitiated = (int)$this->shareRequest->requester_id === (int)$this->shareRequest->from_medecin_id;
        $patient = $this->shareRequest->dossierMedical->patient;

        if ($ownerInitiated) {
            $message = 'Dr ' . ($this->shareRequest->fromMedecin->prenom ?? '') . ' ' . ($this->shareRequest->fromMedecin->name ?? '')
                . ' souhaite partager des éléments de son dossier avec vous (patient: ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . ').';
        } else {
            $message = 'Dr ' . ($this->shareRequest->requester->prenom ?? '') . ' ' . ($this->shareRequest->requester->name ?? '')
                . ' demande l\'accès à votre section du dossier de ' . ($patient->prenom ?? '') . ' ' . ($patient->nom ?? '') . '.';
        }

        return [
            'title' => $ownerInitiated ? 'Partage de dossier' : 'Demande d\'accès au dossier',
            'message' => $message,
            'url' => route('dossiers-medicaux.show', $patient),
            'icon' => $ownerInitiated ? 'bi-share' : 'bi-person-plus',
            'share_request_id' => $this->shareRequest->id,
        ];
    }
}
