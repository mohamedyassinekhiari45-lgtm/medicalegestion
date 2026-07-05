<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarif;
use Barryvdh\DomPDF\Facade\Pdf;

class TarifController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarif::query();
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($w) use ($q) {
                $w->where('code_nomenclature', 'like', "%{$q}%")
                  ->orWhere('acte', 'like', "%{$q}%");
            });
        }
        $tarifs = $query->latest()->paginate(15);
        return view('tarifs.index', compact('tarifs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code_nomenclature' => 'required|string|max:50|unique:tarifs,code_nomenclature',
            'acte' => 'required|string|max:255',
            'montant_ht' => 'required|numeric|min:0',
            'tva' => 'required|numeric|min:0|max:100',
        ]);

        $data['montant_ttc'] = $data['montant_ht'] * (1 + $data['tva'] / 100);
        Tarif::create($data);

        return redirect()->route('tarifs.index')
            ->with('success', 'Tarif créé avec succès.');
    }

    public function exportPdf(Request $request)
    {
        $query = Tarif::query();
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function($w) use ($q) {
                $w->where('code_nomenclature', 'like', "%{$q}%")
                  ->orWhere('acte', 'like', "%{$q}%");
            });
        }
        $tarifs = $query->latest()->get();
        $pdf = Pdf::loadView('tarifs.pdf', compact('tarifs'));
        return $pdf->download('grille-tarifaire.pdf');
    }

    public function edit(Tarif $tarif)
    {
        return view('tarifs.edit', compact('tarif'));
    }

    public function update(Request $request, Tarif $tarif)
    {
        $data = $request->validate([
            'code_nomenclature' => 'required|string|max:50|unique:tarifs,code_nomenclature,' . $tarif->id,
            'acte' => 'required|string|max:255',
            'montant_ht' => 'required|numeric|min:0',
            'tva' => 'required|numeric|min:0|max:100',
        ]);

        $data['montant_ttc'] = $data['montant_ht'] * (1 + $data['tva'] / 100);
        $tarif->update($data);

        return redirect()->route('tarifs.index')
            ->with('success', 'Tarif modifié avec succès.');
    }

    public function destroy(Tarif $tarif)
    {
        if ($tarif->consultations()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : ce tarif a déjà été utilisé dans des consultations.');
        }
        $tarif->delete();
        return redirect()->route('tarifs.index')
            ->with('success', 'Tarif supprimé.');
    }
}
