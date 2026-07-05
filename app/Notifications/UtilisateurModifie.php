<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class UtilisateurModifie extends Notification
{
    use Queueable;

    protected $user;
    protected $action;

    public function __construct(User $user, $action)
    {
        $this->user = $user;
        $this->action = $action;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $labels = [
            'update' => ['title' => 'Utilisateur modifié', 'msg' => 'a été modifié.'],
            'delete' => ['title' => 'Utilisateur supprimé', 'msg' => 'a été supprimé définitivement.'],
            'activate' => ['title' => 'Utilisateur activé', 'msg' => 'a été activé.'],
            'deactivate' => ['title' => 'Utilisateur désactivé', 'msg' => 'a été désactivé.'],
        ];
        $info = $labels[$this->action] ?? ['title' => 'Utilisateur', 'msg' => 'a été modifié.'];

        return [
            'title' => $info['title'],
            'message' => 'L\'utilisateur ' . ($this->user->prenom ?? '') . ' ' . $this->user->name . ' (' . $this->user->role . ') ' . $info['msg'],
            'url' => route('users.index'),
            'icon' => 'bi-person-badge',
        ];
    }
}
