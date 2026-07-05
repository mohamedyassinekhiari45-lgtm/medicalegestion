<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialite;
use Barryvdh\DomPDF\Facade\Pdf;

class SpecialiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Specialite::withCount('medecins');
        if ($request->filled('search')) {
            $query->where('libelle', 'like', "%{$request->search}%");
        }
        $specialites = $query->latest()->paginate(15);
        return view('specialites.index', compact('specialites'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255|unique:specialites,libelle',
            'description' => 'nullable|string',
        ]);

        Specialite::create($data);
        return redirect()->route('specialites.index')
            ->with('success', 'Spécialité créée avec succès.');
    }

    public function update(Request $request, Specialite $specialite)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255|unique:specialites,libelle,' . $specialite->id,
            'description' => 'nullable|string',
        ]);

        $specialite->update($data);
        return redirect()->route('specialites.index')
            ->with('success', 'Spécialité modifiée avec succès.');
    }

    public function exportPdf(Request $request)
    {
        $query = Specialite::withCount('medecins');
        if ($request->filled('search')) {
            $query->where('libelle', 'like', "%{$request->search}%");
        }
        $specialites = $query->latest()->get();
        $pdf = Pdf::loadView('specialites.pdf', compact('specialites'));
        return $pdf->download('specialites.pdf');
    }

    public function destroy(Specialite $specialite)
    {
        if ($specialite->medecins()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer : des médecins sont rattachés à cette spécialité.');
        }
        $specialite->delete();
        return redirect()->route('specialites.index')
            ->with('success', 'Spécialité supprimée.');
    }
}
