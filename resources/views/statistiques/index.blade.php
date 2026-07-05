@extends('layouts.app')
@section('title', 'Statistiques')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-graph-up"></i> Statistiques</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        <a href="{{ route('statistiques.pdf', request()->all()) }}" class="btn btn-danger">
            <i class="bi bi-filetype-pdf"></i> Export PDF
        </a>
        <a href="{{ route('statistiques.xlsx', request()->all()) }}" class="btn btn-success ms-1">
            <i class="bi bi-file-earmark-excel"></i> Export XLSX
        </a>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <input type="date" name="date_debut" class="form-control" value="{{ $date_debut }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_fin" class="form-control" value="{{ $date_fin }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('statistiques.pdf', ['date_debut' => $date_debut, 'date_fin' => $date_fin]) }}" class="btn btn-danger w-100">
                    <i class="bi bi-filetype-pdf"></i> PDF
                </a>
            </div>
            <div class="col-md-2">
                <a href="{{ route('statistiques.xlsx', ['date_debut' => $date_debut, 'date_fin' => $date_fin]) }}" class="btn btn-success w-100">
                    <i class="bi bi-file-earmark-excel"></i> XLSX
                </a>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card text-bg-primary"><div class="card-body"><h5>{{ $stats['patients_count'] }}</h5><p>Patients</p></div></div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-success"><div class="card-body"><h5>{{ $stats['total_consultations'] }}</h5><p>Consultations</p></div></div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-warning"><div class="card-body"><h5>{{ $stats['total_rdv'] }}</h5><p>Rendez-vous</p></div></div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-info"><div class="card-body"><h5>{{ number_format($stats['total_recettes'], 2) }} DT</h5><p>Recettes payées</p></div></div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-secondary"><div class="card-body"><h5>{{ $stats['factures_non_generees'] }}</h5><p>Factures non générées</p></div></div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-warning"><div class="card-body"><h5>{{ number_format($stats['montant_impaye'], 2) }} DT</h5><p>Montant impayé</p></div></div>
    </div>

</div>
<div class="row">
    <div class="col-md-8 mb-3">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-bar-chart-line"></i> Consultations par jour</div>
            <div class="card-body">
                <canvas id="chartConsultations" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card animate-fade-up animate-delay-1">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Statut des rendez-vous</div>
            <div class="card-body">
                <canvas id="chartRdvStatut" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card animate-fade-up animate-delay-2">
            <div class="card-header"><i class="bi bi-currency-euro"></i> Recettes par jour</div>
            <div class="card-body">
                <canvas id="chartRecettes" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card animate-fade-up animate-delay-2">
            <div class="card-header"><i class="bi bi-trophy"></i> Top 5 médecins</div>
            <div class="card-body">
                <canvas id="chartTopMedecins" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartConsultations'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($consultations_par_jour->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
        datasets: [{
            label: 'Consultations',
            data: {!! json_encode($consultations_par_jour->pluck('total')) !!},
            backgroundColor: 'rgba(44, 123, 229, 0.7)',
            borderColor: '#2c7be5',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

new Chart(document.getElementById('chartRdvStatut'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($rdv_par_statut->pluck('statut')) !!},
        datasets: [{
            data: {!! json_encode($rdv_par_statut->pluck('total')) !!},
            backgroundColor: ['#f4a100', '#2c7be5', '#00ac69', '#e81500'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

new Chart(document.getElementById('chartRecettes'), {
    type: 'line',
    data: {
        labels: {!! json_encode($recettes_par_jour->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))) !!},
        datasets: [{
            label: 'Recettes (DT)',
            data: {!! json_encode($recettes_par_jour->pluck('total')) !!},
            borderColor: '#00ac69',
            backgroundColor: 'rgba(0, 172, 105, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById('chartTopMedecins'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($top_medecins->map(fn($m) => 'Dr ' . $m->prenom)) !!},
        datasets: [{
            label: 'Consultations',
            data: {!! json_encode($top_medecins->pluck('consultations_count')) !!},
            backgroundColor: ['#2c7be5', '#00ac69', '#f4a100', '#e81500', '#6c757d'],
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endpush
@endsection
