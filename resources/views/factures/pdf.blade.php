<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c7be5; padding-bottom: 15px; }
        .header h1 { color: #2c7be5; margin: 0; font-size: 22px; }
        .header p { color: #666; margin: 5px 0 0; }
        .info { width: 100%; margin-bottom: 20px; }
        .info td { padding: 3px 8px; vertical-align: top; }
        .info td:first-child { font-weight: bold; width: 120px; color: #555; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #2c7be5; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        table.items td { padding: 7px 8px; border-bottom: 1px solid #ddd; }
        table.items tr:nth-child(even) td { background: #f8f9fa; }
        .totals { width: 100%; }
        .totals td { padding: 4px 8px; text-align: right; }
        .totals .grand-total td { font-weight: bold; font-size: 14px; color: #2c7be5; border-top: 2px solid #333; padding-top: 8px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #00ac69; color: #fff; }
        .badge-warning { background: #f4a100; color: #fff; }
        .badge-danger { background: #e81500; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion Médicale</h1>
        <p>Système de gestion de cabinet médical</p>
        <h2 style="margin-top:10px;">Facture {{ $facture->numero_facture }}</h2>
    </div>

    <table class="info">
        <tr><td>Patient</td><td>{{ $facture->consultation->patient->prenom }} {{ $facture->consultation->patient->nom }}</td></tr>
        <tr><td>N° Dossier</td><td>{{ $facture->consultation->patient->numero_dossier }}</td></tr>
        <tr><td>Médecin</td><td>Dr {{ $facture->consultation->medecin->prenom }} {{ $facture->consultation->medecin->name }}</td></tr>
        <tr><td>Date d'émission</td><td>{{ $facture->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Statut</td><td><span class="badge badge-{{ $facture->statut_paiement === 'paye' ? 'success' : ($facture->statut_paiement === 'partiel' ? 'warning' : ($facture->statut_paiement === 'genere' ? 'secondary' : 'danger')) }}">{{ $facture->statut_paiement === 'genere' ? 'Générée' : $facture->statut_paiement }}</span></td></tr>
    </table>

    <table class="items">
        <thead><tr><th>Désignation</th><th style="text-align:right;">Montant</th></tr></thead>
        <tbody>
            @foreach($facture->consultation->tarifs as $t)
            <tr><td>{{ $t->acte }}</td><td style="text-align:right;">{{ number_format($t->montant_ttc, 2) }} DT</td></tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td style="width:80%;">Total</td><td>{{ number_format($facture->montant_total, 2) }} DT</td></tr>
        <tr><td>Montant payé</td><td>{{ number_format($facture->montant_paye, 2) }} DT</td></tr>
        <tr class="grand-total"><td>Reste à payer</td><td>{{ number_format($facture->montant_total - $facture->montant_paye, 2) }} DT</td></tr>
    </table>

    @if($facture->paiements->count() > 0)
    <h3 style="margin-top:20px;">Historique des paiements</h3>
    <table class="items">
        <thead><tr><th>Date</th><th>Montant</th><th>Mode</th><th>Référence</th></tr></thead>
        <tbody>
            @foreach($facture->paiements as $p)
            <tr>
                <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                <td>{{ number_format($p->montant, 2) }} DT</td>
                <td>{{ $p->mode_reglement }}</td>
                <td>{{ $p->reference ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Gestion Médicale &mdash; Document généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
