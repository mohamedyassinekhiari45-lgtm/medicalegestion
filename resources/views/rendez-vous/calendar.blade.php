@extends('layouts.app')
@section('title', 'Calendrier des rendez-vous')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-calendar3"></i> Calendrier des rendez-vous</h4>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-1"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
        @if(in_array(Auth::user()->role, ['admin', 'receptionniste']))
        <a href="{{ route('rendez-vous.create') }}" class="btn btn-primary me-1"><i class="bi bi-calendar-plus"></i> Nouveau rendez-vous</a>
        @endif
        <a href="{{ route('rendez-vous.index') }}" class="btn btn-outline-secondary"><i class="bi bi-table"></i> Vue liste</a>
    </div>
</div>

<div class="card animate-fade-up">
    <div class="card-body">
        @if(Auth::user()->role !== 'medecin')
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterMedecin" class="form-select">
                    <option value="">Tous les médecins</option>
                    @foreach($medecins as $m)
                    <option value="{{ $m->id }}">Dr {{ $m->prenom }} {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        <div style="position:relative; min-height:500px;">
            <div id="calendar"></div>
            <div id="calendarLoader" class="text-center py-5" style="display:none; position:absolute; top:0; left:0; right:0; bottom:0; background:var(--card-bg); opacity:0.92; z-index:10; padding-top:200px;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Chargement...</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-3 d-flex gap-3 flex-wrap">
    <span class="badge fs-6" style="background:#f59e0b;">Planifié</span>
    <span class="badge fs-6" style="background:#6366f1;">Confirmé</span>
    <span class="badge fs-6" style="background:#06b6d4;">En cours</span>
    <span class="badge fs-6" style="background:#10b981;">Terminé</span>
    <span class="badge fs-6" style="background:#ef4444;">Annulé</span>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    #calendar { min-height: 500px; }
    .fc-event { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    let calendar = null;
    try {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            firstDay: 1,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            events: function(info, successCallback, failureCallback) {
                const medecinId = document.getElementById('filterMedecin')?.value;
                let url = '{{ route("api.events") }}?start=' + info.startStr + '&end=' + info.endStr;
                if (medecinId) url += '&medecin_id=' + medecinId;
                fetch(url)
                    .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(successCallback)
                    .catch(failureCallback);
            },
            eventClick: function (info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            loading: function (isLoading) {
                const loader = document.getElementById('calendarLoader');
                if (loader) loader.style.display = isLoading ? 'block' : 'none';
            }
        });
        calendar.render();
    } catch (e) {
        calendarEl.innerHTML = '<div class="alert alert-danger">Erreur calendrier: ' + e.message + '</div>';
    }

    const filterMedecin = document.getElementById('filterMedecin');
    if (filterMedecin && calendar) {
        filterMedecin.addEventListener('change', function () {
            calendar.refetchEvents();
        });
    }
});
</script>
@endpush
