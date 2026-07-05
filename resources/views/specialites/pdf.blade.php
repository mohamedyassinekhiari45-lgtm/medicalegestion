<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Spécialités</title>
<style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ddd;text-align:left}th{background:#2c7be5;color:#fff}</style>
</head>
<body>
<h2>Spécialités médicales</h2>
<p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
<table>
<thead><tr><th>Libellé</th><th>Description</th><th>Médecins</th></tr></thead>
<tbody>
@foreach($specialites as $s)
<tr>
<td>{{ $s->libelle }}</td>
<td>{{ $s->description ?? '-' }}</td>
<td>{{ $s->medecins_count }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
