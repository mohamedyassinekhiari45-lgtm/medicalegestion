<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications->count()
        ]);
    }

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();
        return back()->with('success', 'Notification supprimée.');
    }

    public function destroyAll()
    {
        Auth::user()->notifications()->delete();
        return back()->with('success', 'Toutes les notifications ont été supprimées.');
    }

    public function dropdown()
    {
        $notifications = Auth::user()->unreadNotifications()->take(5)->get();
        $html = view('notifications.dropdown', compact('notifications'))->render();
        return response()->json(['html' => $html, 'count' => Auth::user()->unreadNotifications->count()]);
    }
}
