@extends('layouts.etudiant')

@section('title', 'Mes notes')
@section('page-title', 'Mes notes par module')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <ul class="nav nav-tabs card-header-tabs" id="semestreTab" role="tablist">
            @foreach($semestresPossibles as $semestre)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $semestreActif == $semestre ? 'active' : '' }}" 
                   href="{{ route('etudiant.notes', ['semestre' => $semestre]) }}" 
                   role="tab">
                    Semestre {{ $semestre }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        @if(!empty($notesParSemestre[$semestreActif]))
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Module</th>
                            <th>Contrôle continu</th>
                            <th>Examen final</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notesParSemestre[$semestreActif] as $item)
                        <tr>
                            <td><strong>{{ $item->module->nom }}</strong></td>
                            <td>{{ $item->controle_continu ?? '-' }} /20</td>
                            <td>{{ $item->examen_finale ?? '-' }} /20</td>
                            <td>
                                @if($item->moyenne !== null)
                                    <span class="badge bg-primary">{{ $item->moyenne }} /20</span>
                                @else
                                    <span class="text-muted">Non saisie</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <strong>Moyenne du semestre {{ $semestreActif }} :</strong> {{ $moyennesParSemestre[$semestreActif] }} /20
            </div>
            <div class="alert alert-success mt-2">
                <strong>Moyenne générale :</strong> {{ $moyenneGenerale }} /20
            </div>
        @else
            <div class="alert alert-warning">
                Aucune note trouvée pour ce semestre.
            </div>
        @endif
    </div>
</div>
@endsection