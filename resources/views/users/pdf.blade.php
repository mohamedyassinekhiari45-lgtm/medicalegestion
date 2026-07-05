<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $titre }}</title>
<style>table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #ddd;text-align:left}th{background:#2c7be5;color:#fff}</style>
</head>
<body>
<h2>{{ $titre }}</h2>
<p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
<table>
<thead><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Rôle</th><th>Statut</th></tr></thead>
<tbody>
@foreach($users as $u)
<tr>
<td>{{ $u->name }}</td>
<td>{{ $u->prenom }}</td>
<td>{{ $u->email }}</td>
<td>{{ $u->role }}</td>
<td>{{ $u->statut ? 'Actif' : 'Inactif' }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
