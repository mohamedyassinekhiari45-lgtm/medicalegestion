<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Exports\ActivityLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        } else {
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('role')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('role', $request->role);
                });
            }
        }
        if ($request->action) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        $logs = $query->paginate(30);
        $users = User::orderBy('name')->get(['id', 'prenom', 'name', 'role', 'last_seen_at']);
        $selectedUser = $request->user_id ? User::find($request->user_id, ['id', 'prenom', 'name', 'role']) : null;
        return view('activity-logs.index', compact('logs', 'users', 'selectedUser'));
    }

    public function exportXlsx(Request $request)
    {
        return Excel::download(new ActivityLogExport($request, auth()->user()), 'journal-activite.xlsx');
    }
}
