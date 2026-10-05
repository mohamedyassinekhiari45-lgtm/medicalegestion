<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RendezVous;
use App\Models\Patient;
use App\Models\User;
use App\Models\Specialite;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RendezVousExport;
use App\Notifications\NouveauRendezVous;
use App\Notifications\RendezVousAnnule;
use App\Notifications\RendezVousModifie;
use App\Models\ActivityLog;

class RendezVousController extends Controller
{
    public function index(Request $request)
    {
        $query = RendezVous::with(['patient', 'medecin.specialites']);

        if (Auth::user()->role === 'medecin') {
            $query->where('medecin_id', Auth::id());
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_rdv', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_rdv', '<=', $request->date_fin);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('medecin_id')) {
            $query->where('medecin_id', $request->medecin_id);
        }
        if ($request->filled('medecin_nom')) {
            $nom = $request->medecin_nom;
            $query->whereHas('medecin', function ($q) use ($nom) {
                $q->where('name', 'like', "%{$nom}%")
                  ->orWhere('prenom', 'like', "%{$nom}%");
            });
        }
        if ($request->filled('specialite_id')) {
            $query->whereHas('medecin.specialites', function ($q) use ($request) {
                $q->where('specialites.id', $request->specialite_id);
            });
        }

        $rendezVous = $query->orderBy('date_rdv')->orderBy('heure_rdv')->paginate(20);
        $medecins = User::where('role', 'medecin')->where('statut', true)->get();
        $specialites = Specialite::all();

        return view('rendez-vous.index', compact('rendezVous', 'medecins', 'specialites'));
    }

    public function show(RendezVous $rendezVous)
    {
        $rendezVous->load(['patient', 'medecin.specialites', 'consultation']);
        return view('rendez-vous.show', compact('rendezVous'));
    }

    public function create()
    {
        $patients = Patient::all();
        $medecins = User::where('role', 'medecin')->where('statut', true)->get();
        $specialites = Specialite::all();
        return view('rendez-vous.create', compact('patients', 'medecins', 'specialites'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medecin_id' => 'required|exists:users,id',
            'date_rdv' => 'required|date|after_or_equal:today',
            'heure_rdv' => 'required',
            'motif' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $conflit = RendezVous::where('medecin_id', $data['medecin_id'])
            ->whereDate('date_rdv', $data['date_rdv'])
            ->whereTime('heure_rdv', $data['heure_rdv'])
            ->whereIn('statut', ['planifie', 'confirme', 'en_cours', 'termine'])
            ->exists();

        if ($conflit) {
            return back()->withErrors(['heure_rdv' => 'Ce créneau est déjà réservé.'])->withInput();
        }

        $data['cree_par'] = Auth::id();
        $data['statut'] = 'planifie';
        $rendezVous = RendezVous::create($data);

        $medecin = User::find($data['medecin_id']);
        if ($medecin) {
            $medecin->notify(new NouveauRendezVous($rendezVous));
        }
        ActivityLog::log('create', 'Rendez-vous planifié pour le patient #' . $rendezVous->patient_id . ' le ' . $rendezVous->date_rdv->format('d/m/Y'), 'rendez_vous', $rendezVous->id);

        return redirect()->route('rendez-vous.index')
            ->with('success', 'Rendez-vous planifié avec succès.');
    }

    public function edit(RendezVous $rendezVous)
    {
        $patients = Patient::all();
        $medecins = User::where('role', 'medecin')->where('statut', true)->get();
        $specialites = Specialite::all();
        return view('rendez-vous.edit', compact('rendezVous', 'patients', 'medecins', 'specialites'));
    }

    public function update(Request $request, RendezVous $rendezVous)
    {
        if ($rendezVous->statut === 'en_cours') {
            return redirect()->route('rendez-vous.index')
                ->with('error', 'Impossible de modifier un rendez-vous en cours.');
        }
        if (in_array($rendezVous->statut, ['termine', 'annule'])) {
            return redirect()->route('rendez-vous.index')
                ->with('error', 'Impossible de modifier un rendez-vous ' . $rendezVous->statut . '.');
        }
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medecin_id' => 'required|exists:users,id',
            'date_rdv' => 'required|date',
            'heure_rdv' => 'required',
            'motif' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data['statut'] = $rendezVous->statut;

        $conflit = RendezVous::where('medecin_id', $data['medecin_id'])
            ->whereDate('date_rdv', $data['date_rdv'])
            ->whereTime('heure_rdv', $data['heure_rdv'])
            ->where('id', '!=', $rendezVous->id)
            ->whereIn('statut', ['planifie', 'confirme', 'en_cours', 'termine'])
            ->exists();

        if ($conflit) {
            return back()->withErrors(['heure_rdv' => 'Ce créneau est déjà réservé.'])->withInput();
        }

        $rendezVous->update($data);

        $medecin = User::find($rendezVous->medecin_id);
        if ($medecin) {
            $medecin->notify(new RendezVousModifie($rendezVous));
        }
        ActivityLog::log('update', 'Rendez-vous #' . $rendezVous->id . ' modifié pour le ' . $rendezVous->date_rdv->format('d/m/Y'), 'rendez_vous', $rendezVous->id);

        return redirect()->route('rendez-vous.index')
            ->with('success', 'Rendez-vous modifié avec succès.');
    }

    public function destroy(RendezVous $rendezVous)
    {
        if (!in_array(Auth::user()->role, ['admin', 'receptionniste'])) {
            return redirect()->route('rendez-vous.index')
                ->with('error', 'Vous n\'avez pas le droit d\'annuler un rendez-vous.');
        }
        if ($rendezVous->statut === 'en_cours') {
            return redirect()->route('rendez-vous.index')
                ->with('error', 'Impossible d\'annuler un rendez-vous en cours.');
        }
        $rendezVous->update(['statut' => 'annule']);

        $medecin = User::find($rendezVous->medecin_id);
        if ($medecin) {
            $medecin->notify(new RendezVousAnnule($rendezVous));
        }
        ActivityLog::log('delete', 'Rendez-vous #' . $rendezVous->id . ' annulé par ' . Auth::user()->role, 'rendez_vous', $rendezVous->id);

        return redirect()->route('rendez-vous.index')
            ->with('success', 'Rendez-vous annulé.');
    }

    public function calendar()
    {
        $medecins = User::where('role', 'medecin')->where('statut', true)->get();
        return view('rendez-vous.calendar', compact('medecins'));
    }

    public function apiEvents(Request $request)
    {
        $start = $request->start ?: now()->startOfMonth()->format('Y-m-d');
        $end = $request->end ?: now()->endOfMonth()->format('Y-m-d');

        $query = RendezVous::with(['patient', 'medecin'])
            ->whereDate('date_rdv', '>=', $start)
            ->whereDate('date_rdv', '<=', $end);

        if (Auth::user()->role === 'medecin') {
            $query->where('medecin_id', Auth::id());
        }
        if ($request->medecin_id) {
            $query->where('medecin_id', $request->medecin_id);
        }

        $isMedecin = Auth::user()->role === 'medecin';

        $events = $query->get()->map(function ($rdv) use ($isMedecin) {
            $colors = [
                'planifie' => '#f4a100',
                'confirme' => '#2c7be5',
                'en_cours' => '#00a3e8',
                'termine' => '#00ac69',
                'annule' => '#e81500',
            ];
            $labels = [
                'planifie' => 'Planifié',
                'confirme' => 'Confirmé',
                'en_cours' => 'En cours',
                'termine' => 'Terminé',
                'annule' => 'Annulé',
            ];
            return [
                'id' => $rdv->id,
                'title' => '[' . ($labels[$rdv->statut] ?? $rdv->statut) . '] ' . ($rdv->patient->prenom ?? 'Supprimé') . ' ' . ($rdv->patient->nom ?? ''),
                'start' => $rdv->date_rdv->format('Y-m-d') . 'T' . $rdv->heure_rdv,
                'backgroundColor' => $colors[$rdv->statut] ?? '#6c757d',
                'borderColor' => $colors[$rdv->statut] ?? '#6c757d',
                'url' => $isMedecin ? route('rendez-vous.show', $rdv) : route('rendez-vous.edit', $rdv),
                'extendedProps' => [
                    'statut' => $rdv->statut,
                    'motif' => $rdv->motif,
                    'medecin' => 'Dr ' . ($rdv->medecin->prenom ?? '') . ' ' . ($rdv->medecin->name ?? ''),
                    'patient' => ($rdv->patient->prenom ?? 'Supprimé') . ' ' . ($rdv->patient->nom ?? ''),
                ]
            ];
        });

        return response()->json($events);
    }

    public function apiDisponibilites(Request $request)
    {
        $medecin_id = $request->medecin_id;
        $date = $request->date;

        $occupes = RendezVous::where('medecin_id', $medecin_id)
            ->whereDate('date_rdv', $date)
            ->whereIn('statut', ['planifie', 'confirme', 'en_cours', 'termine'])
            ->get(['heure_rdv', 'statut']);

        $occupesMap = [];
        foreach ($occupes as $r) {
            $h = substr($r->heure_rdv, 0, 5);
            $occupesMap[$h] = $r->statut;
        }

        $creneaux = [];
        $debut = 8;
        $fin = 18;
        for ($h = $debut; $h < $fin; $h++) {
            foreach (['00', '30'] as $min) {
                $creneau = sprintf('%02d:%s', $h, $min);
                $statut = $occupesMap[$creneau] ?? null;
                $creneaux[] = [
                    'heure' => $creneau,
                    'disponible' => !$statut,
                    'statut' => $statut
                ];
            }
        }
        return response()->json($creneaux);
    }

    public function confirm(RendezVous $rendezVous)
    {
        if ($rendezVous->statut !== 'planifie') {
            return back()->with('error', 'Seul un rendez-vous planifié peut être confirmé.');
        }
        if (Auth::user()->role !== 'medecin') {
            return back()->with('error', 'Seul le médecin peut confirmer un rendez-vous.');
        }

        $rendezVous->update(['statut' => 'confirme']);
        ActivityLog::log('update', 'Rendez-vous #' . $rendezVous->id . ' confirmé par le médecin', 'rendez_vous', $rendezVous->id);

        return redirect()->route('dashboard')
            ->with('success', 'Rendez-vous confirmé. Vous pouvez maintenant démarrer la consultation.');
    }

    public function unconfirm(RendezVous $rendezVous)
    {
        if ($rendezVous->statut !== 'confirme') {
            return back()->with('error', 'Seul un rendez-vous confirmé peut être déconfirmé.');
        }
        if (Auth::user()->role !== 'medecin') {
            return back()->with('error', 'Seul le médecin peut déconfirmer un rendez-vous.');
        }

        $rendezVous->update(['statut' => 'planifie']);
        ActivityLog::log('update', 'Rendez-vous #' . $rendezVous->id . ' déconfirmé par le médecin', 'rendez_vous', $rendezVous->id);

        return redirect()->route('dashboard')
            ->with('success', 'Confirmation annulée. Le rendez-vous repasse en statut planifié.');
    }

    public function cancelByDoctor(RendezVous $rendezVous)
    {
        if (!in_array($rendezVous->statut, ['planifie', 'confirme'])) {
            return back()->with('error', 'Ce rendez-vous ne peut pas être annulé.');
        }
        if (Auth::user()->role !== 'medecin') {
            return back()->with('error', 'Seul le médecin peut annuler ce rendez-vous.');
        }

        $rendezVous->update(['statut' => 'annule']);

        $receptionnistes = User::where('role', 'receptionniste')->get();
        foreach ($receptionnistes as $r) {
            $r->notify(new RendezVousAnnule($rendezVous));
        }
        ActivityLog::log('delete', 'Rendez-vous #' . $rendezVous->id . ' annulé par le médecin', 'rendez_vous', $rendezVous->id);

        return redirect()->route('dashboard')
            ->with('success', 'Rendez-vous annulé.');
    }

    public function apiMedecins(Request $request)
    {
        $q = $request->q;
        $specialite_id = $request->specialite_id;
        $medecins = User::where('role', 'medecin')
            ->where('statut', true)
            ->when($specialite_id, function ($query) use ($specialite_id) {
                $query->whereHas('specialites', function ($q) use ($specialite_id) {
                    $q->where('specialites.id', $specialite_id);
                });
            })
            ->where(function ($query) use ($q) {
                if ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('prenom', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                }
            })
            ->with('specialites')
            ->get(['id', 'name', 'prenom', 'email']);
        return response()->json($medecins);
    }

    public function exportPdf(Request $request)
    {
        $query = RendezVous::with(['patient', 'medecin.specialites']);

        if ($request->filled('date_debut')) {
            $query->whereDate('date_rdv', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_rdv', '<=', $request->date_fin);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('medecin_id')) {
            $query->where('medecin_id', $request->medecin_id);
        }

        $rendezVous = $query->orderBy('date_rdv')->orderBy('heure_rdv')->get();

        $pdf = Pdf::loadView('rendez-vous.pdf', compact('rendezVous'));
        return $pdf->download('rendez-vous.pdf');
    }

    public function exportXlsx(Request $request)
    {
        return Excel::download(new RendezVousExport($request), 'rendez-vous.xlsx');
    }
}
