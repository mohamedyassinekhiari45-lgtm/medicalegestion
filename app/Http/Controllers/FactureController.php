<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Paiement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Notifications\FactureGeneree;
use App\Notifications\PaiementRecu;
use App\Models\ActivityLog;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $query = Facture::with(['consultation.patient', 'generateur', 'consultation.medecin']);

        if ($request->filled('statut')) {
            $query->where('statut_paiement', $request->statut);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $factures = $query->latest()->paginate(20);
        return view('factures.index', compact('factures'));
    }

    public function create(Request $request)
    {
        $patient_id = $request->patient_id;
        $search = $request->search;

        $consultations = Consultation::where('statut', 'termine')
            ->whereDoesntHave('facture')
            ->with(['patient', 'medecin', 'tarifs']);

        if ($patient_id) {
            $consultations->where('patient_id', $patient_id);
        }

        if ($search) {
            $consultations->whereHas('patient', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%");
            });
        }

        $consultations = $consultations->get();
        $patients = Patient::all();
        return view('factures.create', compact('consultations', 'patients', 'patient_id', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'montant_total' => 'required|numeric|min:0',
        ]);

        $consultation = Consultation::findOrFail($data['consultation_id']);
        if ($consultation->facture) {
            return back()->with('error', 'Cette consultation a déjà une facture.');
        }

        $data['numero_facture'] = 'FAC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $data['genere_par'] = Auth::id();
        $data['montant_paye'] = 0;
        $data['statut_paiement'] = 'genere';

        $facture = Facture::create($data);
        $consultation->update(['statut' => 'facture']);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new FactureGeneree($facture));
        }
        ActivityLog::log('create', 'Facture #' . $facture->numero_facture . ' générée (' . number_format($facture->montant_total, 2) . ' DT)', 'facture', $facture->id);
        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture générée avec succès.');
    }

    public function generate(Consultation $consultation)
    {
        if ($consultation->statut !== 'termine') {
            return back()->with('error', 'La consultation doit être terminée pour générer une facture.');
        }
        if ($consultation->facture) {
            return back()->with('error', 'Cette consultation a déjà une facture.');
        }

        $montant = $consultation->tarifs->sum('montant_ttc');
        if ($montant <= 0) {
            return back()->with('error', 'Ajoutez des prestations à la consultation avant de facturer.');
        }

        $facture = Facture::create([
            'consultation_id' => $consultation->id,
            'numero_facture' => 'FAC-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
            'montant_total' => $montant,
            'montant_paye' => 0,
            'statut_paiement' => 'genere',
            'genere_par' => Auth::id(),
        ]);

        $consultation->update(['statut' => 'facture']);

        return redirect()->route('factures.show', $facture)
            ->with('success', 'Facture générée automatiquement depuis la consultation.');
    }

    public function show(Facture $facture)
    {
        $facture->load(['consultation.patient', 'consultation.tarifs', 'consultation.medecin', 'generateur', 'paiements.encaisseur']);
        return view('factures.show', compact('facture'));
    }

    public function paiement(Request $request, Facture $facture)
    {
        $data = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_reglement' => 'required|in:especes,cheque,carte_bancaire,virement',
            'reference' => 'nullable|string|max:255',
        ]);

        $data['facture_id'] = $facture->id;
        $data['encaisse_par'] = Auth::id();
        $data['date_paiement'] = today();

        $paiement = Paiement::create($data);

        $totalPaye = $facture->paiements()->sum('montant');
        $facture->update([
            'montant_paye' => $totalPaye,
            'statut_paiement' => $totalPaye >= $facture->montant_total ? 'paye' : 'partiel',
        ]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new PaiementRecu($facture, $paiement));
        }
        ActivityLog::log('update', 'Paiement de ' . number_format($data['montant'], 2) . ' DT enregistré sur la facture #' . $facture->numero_facture, 'facture', $facture->id);
        return back()->with('success', 'Paiement enregistré. Reçu #' . $paiement->id);
    }

    public function pdf(Facture $facture)
    {
        $facture->load(['consultation.patient', 'consultation.medecin', 'consultation.tarifs', 'paiements']);
        $pdf = Pdf::loadView('factures.pdf', compact('facture'));
        return $pdf->download('facture-' . $facture->numero_facture . '.pdf');
    }
}
