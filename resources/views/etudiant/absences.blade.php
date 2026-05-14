@extends('layouts.etudiant')

@section('title', 'Mes absences')
@section('page-title', 'Suivi des absences')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-x text-danger"></i> Absences par module</h6>
        @if(isset($semestresPossibles))
        <form method="GET" action="{{ route('etudiant.absences') }}" class="d-inline">
            <select name="semestre" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                @foreach($semestresPossibles as $s)
                    <option value="{{ $s }}" {{ $semestreChoisi == $s ? 'selected' : '' }}>Semestre {{ $s }}</option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    <div class="card-body">
        @if(count($absencesData) > 0)
            @foreach($absencesData as $data)
                <div class="card mb-4 border-{{ $data->seuil_depasse ? 'danger' : 'secondary' }} shadow-sm">
                    <div class="card-header bg-{{ $data->seuil_depasse ? 'danger' : 'light' }} text-{{ $data->seuil_depasse ? 'white' : 'dark' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $data->module->nom }}</strong>
                            <span class="badge bg-{{ $data->seuil_depasse ? 'light text-danger' : 'secondary' }}">
                                {{ $data->nb_absences }} / {{ $data->seances_theoriques }} absences
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Taux d'absence :</strong> {{ $data->taux_absence }}%
                            @if($data->seuil_depasse)
                                <span class="badge bg-danger ms-2">⚠️ Seuil critique dépassé (20%)</span>
                            @elseif($data->taux_absence >= 15)
                                <span class="badge bg-warning text-dark ms-2">Attention : proche du seuil</span>
                            @endif
                        </div>
                        @if($data->absences->count() > 0)
                            <strong>Détail des absences :</strong>
                            <ul class="list-group mt-2">
                                @foreach($data->absences as $absence)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-calendar-x"></i> {{ \Carbon\Carbon::parse($absence->date)->format('d/m/Y') }}</span>
                                        
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">Aucune absence enregistrée pour ce module.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-warning">
                Aucun module trouvé pour le semestre sélectionné.
            </div>
        @endif
    </div>
</div>
@endsection