<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Rendez-vous</title>
<style>table{width:100%;border-collapse:collapse}th,td{padding:6px;border:1px solid #ddd;text-align:left;font-size:12px}th{background:#2c7be5;color:#fff}</style>
</head>
<body>
<h2>Liste des rendez-vous</h2>
<p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
<table>
<thead><tr><th>Date</th><th>Heure</th><th>Patient</th><th>Médecin</th><th>Motif</th><th>Statut</th></tr></thead>
<tbody>
@foreach($rendezVous as $r)
<tr>
<td>{{ $r->date_rdv->format('d/m/Y') }}</td>
<td>{{ substr($r->heure_rdv, 0, 5) }}</td>
<td>{{ optional($r->patient)->prenom ?? 'N/A' }} {{ optional($r->patient)->nom ?? '' }}</td>
<td>Dr {{ optional($r->medecin)->prenom ?? 'N/A' }} {{ optional($r->medecin)->name ?? '' }}</td>
<td>{{ $r->motif ?? '-' }}</td>
<td>{{ $r->statut }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
