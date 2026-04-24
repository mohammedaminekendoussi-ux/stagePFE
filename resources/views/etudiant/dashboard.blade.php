{{-- resources/views/etudiant/dashboard.blade.php --}}
@extends('layouts.etudiant')

@section('title', 'Tableau de bord')
@section('page-title', 'Mon tableau de bord')

@section('content')
    <!-- Informations personnelles -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><i class="bi bi-person-circle"></i> Mes informations</h5>
                    <table class="table table-borderless">
                        <tr><th>Nom :</th><td>{{ $info['nom'] }}</td></tr>
                        <tr><th>Prénom :</th><td>{{ $info['prenom'] }}</td></tr>
                        <tr><th>Email :</th><td>{{ $info['email'] }}</td></tr>
                        <tr><th>Filière :</th><td>{{ $info['filiere'] }}</td></tr>
                        <tr><th>Groupe :</th><td>{{ $info['groupe'] }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- La carte de la semaine a été supprimée d'ici -->
    </div>

    <!-- Emploi du temps -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar3 text-primary"></i>
                    Emploi du temps — {{ $info['groupe'] }} ({{ $semestreActuel }})
                </h6>
                @php
                    $debutSemaine = now()->startOfWeek()->format('d/m');
                    $finSemaine = now()->endOfWeek()->format('d/m');
                @endphp
                <span class="text-muted">
                    <i class="bi bi-calendar-week"></i> Semaine du {{ $debutSemaine }} au {{ $finSemaine }}
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 text-center align-middle" style="min-width:900px;">
                    <thead>
                        <tr class="table-light">
                            <th class="fw-bold" style="width:130px;">Jour / Horaire</th>
                            @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                                <th class="fw-bold">{{ str_replace('-', ' - ', $creneau) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'] as $jour)
                        <tr>
                            <td class="fw-bold bg-light">{{ $jour }}</td>
                            @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                                <td class="p-2">
                                    @if(isset($emploi[$jour][$creneau]) && $emploi[$jour][$creneau])
                                        @php $seance = $emploi[$jour][$creneau]; @endphp
                                        <div class="rounded p-2 text-white" style="background:linear-gradient(135deg,#28a745,#20c997);">
                                            <div class="fw-bold small">{{ $seance->module->nom }}</div>
                                            <div class="small opacity-75">
                                                <i class="bi bi-person"></i> {{ $seance->formateur->prenom }} {{ $seance->formateur->nom }}
                                            </div>
                                            <div class="small opacity-75">
                                                <i class="bi bi-geo-alt"></i> {{ $seance->salle }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted">—</div>
                                    @endif
                                </td>
                            @endforeach
                        </td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection