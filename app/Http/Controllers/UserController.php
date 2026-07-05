<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Specialite;
use App\Models\Consultation;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\RendezVous;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

use App\Models\ActivityLog;
use App\Notifications\ProfilModifie;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if (auth()->user()->role === 'admin' && auth()->id() !== 1) {
            $query->where(function($q) {
                $q->where('id', 1)->orWhere('role', '!=', 'admin');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('prenom', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $users = $query->with('specialites')->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $specialites = Specialite::all();
        return view('users.create', compact('specialites'));
    }

    public function createWithRole($role)
    {
        if (!in_array($role, ['admin', 'medecin', 'receptionniste'])) {
            return redirect()->route('users.index')->with('error', 'Rôle invalide.');
        }
        if ($role === 'admin' && auth()->id() !== 1) {
            return redirect()->route('users.index')->with('error', 'Seul le super administrateur peut créer un administrateur.');
        }
        $specialites = Specialite::all();
        return view('users.create', compact('specialites', 'role'));
    }

    public function store(Request $request)
    {
        if ($request->role === 'admin' && auth()->id() !== 1) {
            return back()->with('error', 'Seul le super administrateur peut créer un administrateur.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,medecin,receptionniste',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
        }

        $user = User::create($data);

        if ($request->hasFile('avatar')) {
            $filename = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
            $request->file('avatar')->storeAs('public/avatars', $filename);
            $user->update(['avatar' => $filename]);
        }

        if ($request->role === 'medecin' && $request->filled('specialites')) {
            $user->specialites()->sync($request->specialites);
        }

        ActivityLog::log('create', 'Utilisateur créé : ' . $user->name . ' (' . $user->role . ')', 'user', $user->id);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $specialites = Specialite::all();
        $user->load('specialites');
        return view('users.edit', compact('user', 'specialites'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'admin' && $user->id !== auth()->id() && auth()->id() !== 1) {
            return back()->with('error', 'Seul le super administrateur peut modifier un autre administrateur.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:20',
        ];

        if ($request->has('email')) {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        if ($user->id !== auth()->id()) {
            $rules['role'] = 'required|in:admin,medecin,receptionniste';
            $rules['statut'] = 'boolean';
        }

        $data = $request->validate($rules);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048']);
            if ($user->avatar) {
                Storage::delete('public/avatars/' . $user->avatar);
            }
            $filename = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
            $request->file('avatar')->storeAs('public/avatars', $filename);
            $data['avatar'] = $filename;
        }

        $user->update($data);

        $user->notify(new ProfilModifie('Votre profil a été mis à jour par un administrateur.'));

        if ($request->role === 'medecin' && $request->filled('specialites')) {
            $user->specialites()->sync($request->specialites);
        } else {
            $user->specialites()->detach();
        }

        ActivityLog::log('update', 'Utilisateur modifié : ' . $user->name . ' (' . $user->role . ')', 'user', $user->id);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur modifié avec succès.');
    }

    public function exportPdf(Request $request)
    {
        $query = User::query();

        if (auth()->user()->role === 'admin' && auth()->id() !== 1) {
            $query->where(function($q) {
                $q->where('id', 1)->orWhere('role', '!=', 'admin');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('prenom', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $users = $query->with('specialites')->latest()->get();
        $titre = $request->role === 'medecin' ? 'Médecins' : 'Utilisateurs';
        $pdf = Pdf::loadView('users.pdf', compact('users', 'titre'));
        return $pdf->download(strtolower($titre) . '.pdf');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($user->role === 'admin' && auth()->id() !== 1) {
            return back()->with('error', 'Seul le super administrateur peut désactiver un administrateur.');
        }
        $user->update(['statut' => false]);
        ActivityLog::log('update', 'Utilisateur désactivé : ' . $user->name . ' (' . $user->role . ')', 'user', $user->id);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur désactivé.');
    }

    public function activate(User $user)
    {
        if ($user->role === 'admin' && auth()->id() !== 1) {
            return back()->with('error', 'Seul le super administrateur peut activer un administrateur.');
        }
        $user->update(['statut' => true]);
        ActivityLog::log('update', 'Utilisateur activé : ' . $user->name . ' (' . $user->role . ')', 'user', $user->id);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur activé.');
    }

    public function forceDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        if ($user->role === 'admin' && auth()->id() !== 1) {
            return back()->with('error', 'Seul le super administrateur peut supprimer un administrateur.');
        }
        $user->specialites()->detach();
        RendezVous::where('medecin_id', $user->id)->update(['medecin_id' => null]);
        RendezVous::where('cree_par', $user->id)->update(['cree_par' => null]);
        Consultation::where('medecin_id', $user->id)->update(['medecin_id' => null]);
        Facture::where('genere_par', $user->id)->update(['genere_par' => null]);
        Paiement::where('encaisse_par', $user->id)->update(['encaisse_par' => null]);
        $user->delete();
        ActivityLog::log('delete', 'Utilisateur supprimé définitivement : ' . $user->name . ' (' . $user->role . ')', 'user', $user->id);
        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé définitivement.');
    }
}
