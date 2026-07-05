<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $request;
    protected $authUser;

    public function __construct($request, $authUser = null)
    {
        $this->request = $request;
        $this->authUser = $authUser;
    }

    public function title(): string
    {
        return 'Journal';
    }

    public function collection()
    {
        $query = ActivityLog::with('user')->latest();
        if ($this->authUser && $this->authUser->role !== 'admin') {
            $query->where('user_id', $this->authUser->id);
        } else {
            if ($this->request->user_id) {
                $query->where('user_id', $this->request->user_id);
            }
            if ($this->request->filled('role')) {
                $query->whereHas('user', function ($q) {
                    $q->where('role', $this->request->role);
                });
            }
        }
        if ($this->request->action) {
            $query->where('action', $this->request->action);
        }
        if ($this->request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $this->request->date_debut);
        }
        if ($this->request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $this->request->date_fin);
        }
        return $query->get();
    }

    public function headings(): array
    {
        if ($this->authUser && $this->authUser->role !== 'admin') {
            return ['Date', 'Action', 'Description'];
        }
        return ['Date', 'Utilisateur', 'Rôle', 'Action', 'Description'];
    }

    public function map($log): array
    {
        $labels = [
            'create' => 'Création', 'update' => 'Modification', 'delete' => 'Suppression',
            'login' => 'Connexion', 'logout' => 'Déconnexion',
        ];
        $roles = ['admin' => 'Admin', 'medecin' => 'Médecin', 'receptionniste' => 'Récep.'];
        if ($this->authUser && $this->authUser->role !== 'admin') {
            return [
                $log->created_at->format('d/m/Y H:i'),
                $labels[$log->action] ?? $log->action,
                $log->description,
            ];
        }
        return [
            $log->created_at->format('d/m/Y H:i'),
            $log->user->prenom . ' ' . $log->user->name,
            $roles[$log->user->role] ?? $log->user->role,
            $labels[$log->action] ?? $log->action,
            $log->description,
        ];
    }
}
