<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Grille tarifaire</title>
<style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ddd;text-align:left}th{background:#2c7be5;color:#fff}</style>
</head>
<body>
<h2>Grille tarifaire</h2>
<p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
<table>
<thead><tr><th>Code</th><th>Acte</th><th>HT</th><th>TVA</th><th>TTC</th></tr></thead>
<tbody>
@foreach($tarifs as $t)
<tr>
<td>{{ $t->code_nomenclature }}</td>
<td>{{ $t->acte }}</td>
<td>{{ number_format($t->montant_ht, 2) }} DT</td>
<td>{{ $t->tva }}%</td>
<td>{{ number_format($t->montant_ttc, 2) }} DT</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
