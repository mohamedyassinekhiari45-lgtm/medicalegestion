<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Consultation;
use App\Models\Facture;
use App\Models\User;
use App\Models\Specialite;
use App\Models\Tarif;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $stats = [
                'patients_count' => Patient::count(),
                'medecins_count' => User::where('role', 'medecin')->count(),
                'specialites_count' => Specialite::count(),
                'tarifs_count' => Tarif::count(),
                'factures_generees' => Facture::where('statut_paiement', '!=', 'paye')->count(),
                'factures_payees' => Facture::where('statut_paiement', 'paye')->count(),
                'factures_non_generees' => Consultation::where('statut', 'termine')->whereDoesntHave('facture')->count(),
                'montant_impaye' => Facture::where('statut_paiement', '!=', 'paye')
                    ->selectRaw('COALESCE(SUM(montant_total - montant_paye), 0) as total')
                    ->value('total')
                    + Tarif::whereHas('consultations', function($q) {
                        $q->where('statut', 'termine')->whereDoesntHave('facture');
                    })->sum('montant_ttc'),
                'total_recettes' => Facture::whereIn('statut_paiement', ['paye', 'partiel'])->sum('montant_paye'),
            ];
            return view('dashboard.admin', compact('stats'));
        }

        if ($user->role === 'medecin') {
            $rdv_aujourdhui = RendezVous::with(['patient', 'consultation'])
                ->where('medecin_id', $user->id)
                ->where(function($q) {
                    $q->whereIn('statut', ['planifie', 'en_cours'])
                      ->whereDate('date_rdv', today());
                    $q->orWhere('statut', 'confirme');
                })
                ->orderBy('date_rdv')
                ->orderBy('heure_rdv')
                ->get();
            $consultations_recentes = Consultation::with('patient')
                ->where('medecin_id', $user->id)
                ->where('statut', 'termine')
                ->latest()
                ->take(5)
                ->get();
            $patients_count = Consultation::where('medecin_id', $user->id)
                ->distinct('patient_id')
                ->count('patient_id');
            return view('dashboard.medecin', compact('rdv_aujourdhui', 'consultations_recentes', 'patients_count'));
        }

        if ($user->role === 'receptionniste') {
            $rdv_aujourdhui = RendezVous::whereDate('date_rdv', today())->count();
            $patients_aujourdhui = Patient::whereDate('created_at', today())->count();
            $prochains_rdv = RendezVous::with(['patient', 'medecin'])
                ->whereDate('date_rdv', '>=', today())
                ->whereIn('statut', ['planifie', 'confirme', 'en_cours'])
                ->orderBy('date_rdv')
                ->orderBy('heure_rdv')
                ->take(10)
                ->get();
            return view('dashboard.receptionniste', compact('rdv_aujourdhui', 'patients_aujourdhui', 'prochains_rdv'));
        }

        return redirect('/');
    }
}
