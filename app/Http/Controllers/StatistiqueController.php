<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Consultation;
use App\Models\Facture;
use App\Models\User;
use App\Models\Tarif;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\StatistiquesExport;
use Maatwebsite\Excel\Facades\Excel;

class StatistiqueController extends Controller
{
    public function index(Request $request)
    {
        $date_debut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $date_fin = ($request->date_fin ?? now()->format('Y-m-d')) . ' 23:59:59';

        $stats = [
            'patients_count' => Patient::count(),
            'medecins_count' => User::where('role', 'medecin')->count(),
            'total_consultations' => Consultation::whereBetween('date_consultation', [$date_debut, $date_fin])->count(),
            'total_rdv' => RendezVous::whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'rdv_annules' => RendezVous::where('statut', 'annule')->whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'total_recettes' => Facture::where('statut_paiement', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])->sum('montant_total'),
            'recettes_partiel' => Facture::where('statut_paiement', 'partiel')->whereBetween('created_at', [$date_debut, $date_fin])->sum('montant_total'),
            'factures_generees' => Facture::where('statut_paiement', '!=', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])->count(),
            'montant_impaye' => Facture::where('statut_paiement', '!=', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])
                ->selectRaw('COALESCE(SUM(montant_total - montant_paye), 0) as total')
                ->value('total')
                + Tarif::whereHas('consultations', function($q) {
                    $q->where('statut', 'termine')->whereDoesntHave('facture');
                })->sum('montant_ttc'),
            'factures_non_generees' => Consultation::where('statut', 'termine')->whereDoesntHave('facture')->count(),
        ];

        $consultations_par_jour = Consultation::whereBetween('date_consultation', [$date_debut, $date_fin])
            ->select(DB::raw('DATE(date_consultation) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $top_medecins = User::where('role', 'medecin')
            ->withCount(['consultations' => function($q) use ($date_debut, $date_fin) {
                $q->whereBetween('date_consultation', [$date_debut, $date_fin]);
            }])
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();

        $rdv_par_statut = RendezVous::whereBetween('date_rdv', [$date_debut, $date_fin])
            ->select(DB::raw('statut'), DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();

        $recettes_par_jour = Facture::whereIn('statut_paiement', ['paye', 'partiel'])
            ->whereBetween('created_at', [$date_debut, $date_fin])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(montant_paye) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('statistiques.index', compact('stats', 'consultations_par_jour', 'top_medecins', 'rdv_par_statut', 'recettes_par_jour', 'date_debut', 'date_fin'));
    }

    public function exportPdf(Request $request)
    {
        $date_debut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $date_fin = ($request->date_fin ?? now()->format('Y-m-d')) . ' 23:59:59';

        $stats = [
            'patients_count' => Patient::count(),
            'medecins_count' => User::where('role', 'medecin')->count(),
            'total_consultations' => Consultation::whereBetween('date_consultation', [$date_debut, $date_fin])->count(),
            'total_rdv' => RendezVous::whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'rdv_annules' => RendezVous::where('statut', 'annule')->whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'total_recettes' => Facture::where('statut_paiement', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])->sum('montant_total'),
            'factures_generees' => Facture::where('statut_paiement', '!=', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])->count(),
            'montant_impaye' => Facture::where('statut_paiement', '!=', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])
                ->selectRaw('COALESCE(SUM(montant_total - montant_paye), 0) as total')
                ->value('total')
                + Tarif::whereHas('consultations', function($q) {
                    $q->where('statut', 'termine')->whereDoesntHave('facture');
                })->sum('montant_ttc'),
            'factures_non_generees' => Consultation::where('statut', 'termine')->whereDoesntHave('facture')->count(),
        ];

        $top_medecins = User::where('role', 'medecin')
            ->withCount(['consultations' => function($q) use ($date_debut, $date_fin) {
                $q->whereBetween('date_consultation', [$date_debut, $date_fin]);
            }])
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();

        $pdf = Pdf::loadView('statistiques.pdf', compact('stats', 'top_medecins', 'date_debut', 'date_fin'));
        return $pdf->download('statistiques-' . $date_debut . '-au-' . $date_fin . '.pdf');
    }

    public function exportXlsx(Request $request)
    {
        $date_debut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $date_fin = ($request->date_fin ?? now()->format('Y-m-d')) . ' 23:59:59';

        $stats = [
            'patients_count' => Patient::count(),
            'medecins_count' => User::where('role', 'medecin')->count(),
            'total_consultations' => Consultation::whereBetween('date_consultation', [$date_debut, $date_fin])->count(),
            'total_rdv' => RendezVous::whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'rdv_annules' => RendezVous::where('statut', 'annule')->whereBetween('date_rdv', [$date_debut, $date_fin])->count(),
            'total_recettes' => Facture::where('statut_paiement', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])->sum('montant_total'),
            'montant_impaye' => Facture::where('statut_paiement', '!=', 'paye')->whereBetween('created_at', [$date_debut, $date_fin])
                ->selectRaw('COALESCE(SUM(montant_total - montant_paye), 0) as total')
                ->value('total')
                + Tarif::whereHas('consultations', function($q) {
                    $q->where('statut', 'termine')->whereDoesntHave('facture');
                })->sum('montant_ttc'),
            'factures_non_generees' => Consultation::where('statut', 'termine')->whereDoesntHave('facture')->count(),
        ];

        $top_medecins = User::where('role', 'medecin')
            ->withCount(['consultations' => function($q) use ($date_debut, $date_fin) {
                $q->whereBetween('date_consultation', [$date_debut, $date_fin]);
            }])
            ->orderBy('consultations_count', 'desc')
            ->take(5)
            ->get();

        return Excel::download(
            new StatistiquesExport($stats, $top_medecins, $date_debut, $date_fin),
            'statistiques-' . $date_debut . '-au-' . $date_fin . '.xlsx'
        );
    }
}
