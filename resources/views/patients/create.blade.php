@extends('layouts.app')
@section('title', 'Nouveau Patient')
@section('content')
<a href="{{ route('dashboard') }}" class="btn btn-outline-primary mb-3"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card animate-fade-up">
            <div class="card-header"><i class="bi bi-person-plus"></i> Nouveau Patient</div>
            <div class="card-body">
                <form method="POST" action="{{ route('patients.store') }}">
                    @csrf
                    @include('patients._form')
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary"><i class="bi bi-x"></i> Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection