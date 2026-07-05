<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NouvelUtilisateur extends Notification
{
    use Queueable;

    protected $newUser;

    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Nouvel utilisateur',
            'message' => 'L\'utilisateur ' . ($this->newUser->prenom ?? '') . ' ' . $this->newUser->name . ' (' . $this->newUser->role . ') a été créé.',
            'url' => route('users.index'),
            'icon' => 'bi-person-badge',
        ];
    }
}
