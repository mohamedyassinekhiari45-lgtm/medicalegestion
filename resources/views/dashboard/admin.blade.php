@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('subtitle', 'Vue d\'ensemble du cabinet')
@section('content')
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-1">
        <div class="stat-card stat-card-primary">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <h5>{{ $stats['patients_count'] }}</h5>
                <p>Patients enregistrés</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-2">
        <div class="stat-card stat-card-success">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                <h5>{{ $stats['medecins_count'] }}</h5>
                <p>Médecins actifs</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="stat-card stat-card-warning">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-tags"></i></div>
                <h5>{{ $stats['specialites_count'] }}</h5>
                <p>Spécialités</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-4">
        <div class="stat-card stat-card-info">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-currency-euro"></i></div>
                <h5>{{ $stats['tarifs_count'] }}</h5>
                <p>Tarifs</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-1">
        <div class="stat-card stat-card-secondary">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <h5>{{ $stats['factures_non_generees'] }}</h5>
                <p>Factures non générées</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-2">
        <div class="stat-card stat-card-success">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <h5>{{ $stats['factures_payees'] }}</h5>
                <p>Factures payées</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="stat-card stat-card-warning">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-currency-euro"></i></div>
                <h5>{{ number_format($stats['montant_impaye'], 2) }} DT</h5>
                <p>Montant impayé</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4 animate-fade-up animate-delay-4">
        <div class="stat-card stat-card-info">
            <div class="card-body">
                <div class="stat-icon"><i class="bi bi-currency-euro"></i></div>
                <h5>{{ number_format($stats['total_recettes'], 2) }} DT</h5>
                <p>Total recettes</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-4 animate-fade-up animate-delay-1">
        <div class="stat-card stat-card-primary">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon position-static"><i class="bi bi-clock-history"></i></div>
                <div>
                    <h5 class="mb-0">{{ $stats['factures_non_generees'] }} consultation(s) terminée(s) sans facture</h5>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-3">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightning-charge"></i> Actions rapides</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-primary btn-lg text-start">
                        <i class="bi bi-people"></i> Gérer les patients
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-lg text-start">
                        <i class="bi bi-person-badge"></i> Gérer les utilisateurs
                    </a>
                    <a href="{{ route('users.index', ['role' => 'medecin']) }}" class="btn btn-outline-success btn-lg text-start">
                        <i class="bi bi-heart-pulse"></i> Gérer les médecins
                    </a>
                    <a href="{{ route('specialites.index') }}" class="btn btn-outline-warning btn-lg text-start">
                        <i class="bi bi-tags"></i> Gérer les spécialités
                    </a>
                    <a href="{{ route('tarifs.index') }}" class="btn btn-outline-info btn-lg text-start">
                        <i class="bi bi-currency-euro"></i> Gérer les tarifs
                    </a>
                    <a href="{{ route('statistiques.index') }}" class="btn btn-outline-danger btn-lg text-start">
                        <i class="bi bi-graph-up"></i> Voir les statistiques
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4 animate-fade-up animate-delay-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Aperçu rapide</div>
            <div class="card-body">
                <canvas id="miniChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var miniChart = null;

function renderMiniChart() {
    var html = document.documentElement;
    var isDark = html.getAttribute('data-theme') === 'dark';
    var eyeOn = html.getAttribute('data-eye-comfort') === 'true';
    var gridColor, labelColor, bgColor, lineColor, lineWidth;
    if (isDark && eyeOn) {
        gridColor = 'rgba(255,255,255,0.06)'; labelColor = '#8b7355'; bgColor = 'rgba(212,160,23,0.2)'; lineColor = '#d4a017'; lineWidth = 2;
    } else if (isDark) {
        gridColor = 'rgba(255,255,255,0.08)'; labelColor = '#94a3b8'; bgColor = 'rgba(99,102,241,0.2)'; lineColor = '#818cf8'; lineWidth = 2;
    } else if (eyeOn) {
        gridColor = 'rgba(120,90,50,0.1)'; labelColor = '#8b7355'; bgColor = 'rgba(184,134,11,0.15)'; lineColor = '#b8860b'; lineWidth = 2;
    } else {
        gridColor = 'rgba(0,0,0,0.05)'; labelColor = '#94a3b8'; bgColor = 'rgba(99,102,241,0.08)'; lineColor = '#a5b4fc'; lineWidth = 1.5;
    }

    if (miniChart) { miniChart.destroy(); }

    miniChart = new Chart(document.getElementById('miniChart'), {
        type: 'radar',
        data: {
            labels: ['Patients', 'Médecins', 'Spécialités', 'Tarifs'],
            datasets: [{
                label: 'Aperçu',
                data: [
                    {{ $stats['patients_count'] }},
                    {{ $stats['medecins_count'] }},
                    {{ $stats['specialites_count'] }},
                    {{ $stats['tarifs_count'] }}
                ],
                backgroundColor: bgColor,
                borderColor: lineColor,
                pointBackgroundColor: lineColor,
                borderWidth: lineWidth
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    angleLines: { color: gridColor },
                    pointLabels: { color: labelColor, font: { size: 11 } },
                    ticks: { display: false, backdropColor: 'transparent' }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', renderMiniChart);
document.addEventListener('themechange', renderMiniChart);
</script>
@endpush
@endsection
