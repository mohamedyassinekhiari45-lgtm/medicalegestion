<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statistiques</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c7be5; padding-bottom: 15px; }
        .header h1 { color: #2c7be5; margin: 0; font-size: 22px; }
        .header p { color: #666; margin: 5px 0 0; }
        .stats { width: 100%; margin-bottom: 20px; }
        .stats td { padding: 8px; text-align: center; border: 1px solid #ddd; }
        .stats td h3 { margin: 0; font-size: 20px; color: #2c7be5; }
        .stats td p { margin: 5px 0 0; font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #2c7be5; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion Médicale</h1>
        <p>Rapport statistique du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($date_fin)->format('d/m/Y') }}</p>
    </div>

    <table class="stats">
        <tr>
            <td><h3>{{ $stats['patients_count'] }}</h3><p>Patients</p></td>
            <td><h3>{{ $stats['total_consultations'] }}</h3><p>Consultations</p></td>
            <td><h3>{{ $stats['total_rdv'] }}</h3><p>Rendez-vous</p></td>
            <td><h3>{{ number_format($stats['total_recettes'], 2) }} DT</h3><p>Recettes</p></td>
        </tr>
    </table>

    <table class="stats">
        <tr>
            <td><h3>{{ $stats['factures_non_generees'] }}</h3><p>Factures non générées</p></td>
            <td><h3>{{ number_format($stats['montant_impaye'], 2) }} DT</h3><p>Montant impayé</p></td>
            
        </tr>
    </table>

    <h3>Top 5 médecins</h3>
    <table>
        <thead><tr><th>Médecin</th><th>Consultations</th></tr></thead>
        <tbody>
            @foreach($top_medecins as $m)
            <tr><td>Dr {{ $m->prenom }} {{ $m->name }}</td><td>{{ $m->consultations_count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
