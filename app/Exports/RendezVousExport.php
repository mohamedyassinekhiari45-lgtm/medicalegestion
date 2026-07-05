<?php

namespace App\Exports;

use App\Models\RendezVous;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class RendezVousExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Rendez-vous';
    }

    public function collection()
    {
        $query = RendezVous::with(['patient', 'medecin.specialites']);

        if ($this->request->filled('date_debut')) {
            $query->whereDate('date_rdv', '>=', $this->request->date_debut);
        }
        if ($this->request->filled('date_fin')) {
            $query->whereDate('date_rdv', '<=', $this->request->date_fin);
        }
        if ($this->request->filled('statut')) {
            $query->where('statut', $this->request->statut);
        }
        if ($this->request->filled('medecin_id')) {
            $query->where('medecin_id', $this->request->medecin_id);
        }

        return $query->orderBy('date_rdv')->orderBy('heure_rdv')->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Heure',
            'Patient',
            'Médecin',
            'Spécialités',
            'Motif',
            'Statut',
        ];
    }

    public function map($rdv): array
    {
        return [
            $rdv->date_rdv->format('d/m/Y'),
            substr($rdv->heure_rdv, 0, 5),
            optional($rdv->patient)->prenom . ' ' . optional($rdv->patient)->nom,
            'Dr ' . optional($rdv->medecin)->prenom . ' ' . optional($rdv->medecin)->name,
            $rdv->medecin && $rdv->medecin->specialites->count() > 0
                ? $rdv->medecin->specialites->pluck('libelle')->implode(', ')
                : '',
            $rdv->motif ?? '',
            $rdv->statut,
        ];
    }
}
