<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfilModifie extends Notification
{
    use Queueable;

    protected $description;

    public function __construct($description)
    {
        $this->description = $description;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Profil modifié',
            'message' => $this->description,
            'url' => route('profile'),
            'icon' => 'bi-person-gear',
        ];
    }
}
