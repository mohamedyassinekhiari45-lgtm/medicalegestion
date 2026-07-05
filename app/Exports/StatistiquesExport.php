<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatistiquesExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $stats;
    protected $topMedecins;
    protected $dateDebut;
    protected $dateFin;

    public function __construct($stats, $topMedecins, $dateDebut, $dateFin)
    {
        $this->stats = $stats;
        $this->topMedecins = $topMedecins;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function title(): string
    {
        return 'Statistiques';
    }

    public function headings(): array
    {
        return [
            ['Statistiques du ' . \Carbon\Carbon::parse($this->dateDebut)->format('d/m/Y') . ' au ' . \Carbon\Carbon::parse($this->dateFin)->format('d/m/Y')],
            [],
            ['Indicateur', 'Valeur'],
        ];
    }

    public function array(): array
    {
        $rows = [
            ['Patients', $this->stats['patients_count']],
            ['Médecins', $this->stats['medecins_count']],
            ['Consultations', $this->stats['total_consultations']],
            ['Rendez-vous', $this->stats['total_rdv']],
            ['Rendez-vous annulés', $this->stats['rdv_annules']],
            ['Recettes payées', number_format($this->stats['total_recettes'], 2) . ' DT'],
            ['Factures non générées', $this->stats['factures_non_generees']],
            ['Montant impayé', number_format($this->stats['montant_impaye'], 2) . ' DT'],
        ];

        $rows[] = [];
        $rows[] = ['Top 5 Médecins', 'Consultations'];
        foreach ($this->topMedecins as $m) {
            $rows[] = ['Dr ' . $m->prenom . ' ' . $m->name, $m->consultations_count];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
