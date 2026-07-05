<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\RendezVous;
use App\Models\Patient;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ConsultationTerminee;
use App\Notifications\FactureImpayee;
use App\Models\ActivityLog;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = Consultation::with(['patient', 'medecin']);

        if (Auth::user()->role === 'medecin') {
            $query->where('medecin_id', Auth::id());
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_consultation', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_consultation', '<=', $request->date_fin);
        }
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $consultations = $query->latest()->paginate(20);
        return view('consultations.index', compact('consultations'));
    }

    public function create(Request $request)
    {
        $patient_id = $request->patient_id;
        $rdv_id = $request->rendez_vous_id;
        $patient = $patient_id ? Patient::find($patient_id) : null;
        $rdv = $rdv_id ? RendezVous::find($rdv_id) : null;
        $patients = Patient::all();
        $tarifs = Tarif::all();
        return view('consultations.create', compact('patients', 'patient', 'rdv', 'tarifs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'rendez_vous_id' => 'nullable|exists:rendez_vous,id',
            'date_consultation' => 'required|date',
            'constantes.tension' => 'nullable|string|max:20',
            'constantes.pouls' => 'nullable|integer|min:0|max:300',
            'constantes.temperature' => 'nullable|numeric|min:30|max:45',
            'diagnostic' => 'nullable|string',
            'observations' => 'nullable|string',
            'tarifs' => 'nullable|array',
            'tarifs.*' => 'exists:tarifs,id',
        ]);

        $data['medecin_id'] = Auth::id();
        $data['statut'] = 'termine';
        $data['constantes'] = array_filter($request->constantes ?? [], fn($v) => !is_null($v) && $v !== '');

        $consultation = Consultation::create($data);

        if (!empty($request->tarifs)) {
            $consultation->tarifs()->attach($request->tarifs);
        }

        if ($data['rendez_vous_id']) {
            RendezVous::where('id', $data['rendez_vous_id'])->update(['statut' => 'termine']);
        }

        $receptionnistes = User::where('role', 'receptionniste')->get();
        foreach ($receptionnistes as $r) {
            $r->notify(new ConsultationTerminee($consultation));
        }
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new ConsultationTerminee($consultation));
            $admin->notify(new FactureImpayee($consultation));
        }
        ActivityLog::log('create', 'Consultation créée et terminée pour le patient #' . $consultation->patient_id, 'consultation', $consultation->id);
        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation enregistrée avec succès.');
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'rendez_vous_id' => 'required|exists:rendez_vous,id',
        ]);

        $rdv = RendezVous::findOrFail($data['rendez_vous_id']);
        if ($rdv->statut !== 'confirme') {
            return redirect()->route('rendez-vous.index')
                ->with('error', 'Le rendez-vous doit être confirmé avant de démarrer une consultation.');
        }

        $consultation = Consultation::create([
            'patient_id' => $data['patient_id'],
            'rendez_vous_id' => $data['rendez_vous_id'],
            'medecin_id' => Auth::id(),
            'date_consultation' => now(),
            'statut' => 'en_cours',
        ]);

        RendezVous::where('id', $data['rendez_vous_id'])->update(['statut' => 'en_cours']);

        return redirect()->route('consultations.edit', $consultation);
    }


    public function show(Consultation $consultation)
    {
        $consultation->load(['patient', 'medecin', 'rendezVous', 'tarifs', 'facture']);
        return view('consultations.show', compact('consultation'));
    }

    public function edit(Consultation $consultation)
    {
        $consultation->load(['tarifs', 'facture']);
        $tarifs = Tarif::all();
        return view('consultations.edit', compact('consultation', 'tarifs'));
    }

    public function update(Request $request, Consultation $consultation)
    {
        $data = $request->validate([
            'constantes.tension' => 'nullable|string|max:20',
            'constantes.pouls' => 'nullable|integer|min:0|max:300',
            'constantes.temperature' => 'nullable|numeric|min:30|max:45',
            'diagnostic' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        $data['constantes'] = array_filter($request->constantes ?? [], fn($v) => !is_null($v) && $v !== '');

        $tarifsRaw = $request->input('tarifs');
        $tarifsSync = is_array($tarifsRaw) ? array_values(array_filter($tarifsRaw)) : null;
        $prestationsModifiees = is_array($tarifsRaw);

        if ($request->input('action') === 'terminer') {
            if ($tarifsSync === null) {
                $tarifsSync = $consultation->tarifs()->pluck('tarifs.id')->toArray();
            }
            if (empty($tarifsSync)) {
                return redirect()->route('consultations.edit', $consultation)
                    ->with('error', 'Veuillez sélectionner au moins une prestation avant de terminer la consultation.')
                    ->withErrors(['tarifs' => 'Veuillez sélectionner au moins une prestation avant de terminer la consultation.'])
                    ->withInput();
            }
            $consultation->tarifs()->sync($tarifsSync);
            $data['statut'] = 'termine';
            $consultation->update($data);
            if ($consultation->rendez_vous_id) {
                RendezVous::where('id', $consultation->rendez_vous_id)->update(['statut' => 'termine']);
            }
            $receptionnistes = User::where('role', 'receptionniste')->get();
            foreach ($receptionnistes as $r) {
                $r->notify(new ConsultationTerminee($consultation));
            }
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new ConsultationTerminee($consultation));
                $admin->notify(new FactureImpayee($consultation));
            }
            ActivityLog::log('update', 'Consultation #' . $consultation->id . ' terminée pour le patient #' . $consultation->patient_id, 'consultation', $consultation->id);
            return redirect()->route('consultations.show', $consultation)
                ->with('success', 'Consultation terminée avec succès.');
        }

        if ($prestationsModifiees) {
            $consultation->tarifs()->sync($tarifsSync);
        }
        $consultation->update($data);

        if ($prestationsModifiees) {
            $receptionnistes = User::where('role', 'receptionniste')->get();
            foreach ($receptionnistes as $r) {
                $r->notify(new \App\Notifications\PrestationsModifiees($consultation));
            }
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\PrestationsModifiees($consultation));
            }
        }

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation mise à jour.');
    }
}
