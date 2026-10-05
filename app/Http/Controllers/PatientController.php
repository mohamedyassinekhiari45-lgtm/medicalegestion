<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Models\ActivityLog;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        // Un medecin ne voit que les patients qu'il a consulte
        if ($request->user()->role === 'medecin') {
            $query->consultesPar($request->user()->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('numero_dossier', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }
        $patients = $query->latest()->paginate(15);
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
        ]);

        $data['numero_dossier'] = 'PAT-' . strtoupper(Str::random(8));
        while (Patient::where('numero_dossier', $data['numero_dossier'])->exists()) {
            $data['numero_dossier'] = 'PAT-' . strtoupper(Str::random(8));
        }

        $patient = Patient::create($data);

        ActivityLog::log('create', 'Patient créé : ' . $patient->prenom . ' ' . $patient->nom . ' (' . $patient->numero_dossier . ')', 'patient', $patient->id);
        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient créé avec succès. Numéro dossier: ' . $patient->numero_dossier);
    }

    public function show(Patient $patient)
    {
        if (Auth::user()->role === 'medecin' && ! $patient->estTraitePar(Auth::id())) {
            return redirect()->route('patients.index')
                ->with('error', "Ce patient ne fait pas partie de votre file : consultez-le uniquement depuis « Mes patients ».");
        }

        $patient->load(['rendezVous' => function($q) {
            $q->latest()->with('medecin.specialites');
        }]);
        if (Auth::user()->role === 'medecin') {
            $patient->load(['consultations' => function($q) {
                $q->latest()->with('medecin.specialites');
                $q->where('medecin_id', Auth::id());
            }]);
        }
        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:M,F',
        ]);

        $patient->update($data);
        ActivityLog::log('update', 'Patient modifié : ' . $patient->prenom . ' ' . $patient->nom . ' (' . $patient->numero_dossier . ')', 'patient', $patient->id);
        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient modifié avec succès.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        ActivityLog::log('delete', 'Patient supprimé : ' . $patient->prenom . ' ' . $patient->nom . ' (' . $patient->numero_dossier . ')', 'patient', $patient->id);
        return redirect()->route('patients.index')
            ->with('success', 'Patient supprimé (archivé) avec succès.');
    }

    public function apiSearch(Request $request)
    {
        $search = $request->q;
        $query = Patient::query();

        // Un medecin ne cherche que parmi ses propres patients
        if ($request->user()->role === 'medecin') {
            $query->consultesPar($request->user()->id);
        }

        $patients = $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('numero_dossier', 'like', "%{$search}%");
            })
            ->limit(15)
            ->get(['id', 'numero_dossier', 'nom', 'prenom']);
        return response()->json($patients);
    }
}
