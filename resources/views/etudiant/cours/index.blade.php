@extends('layouts.etudiant')

@section('title', 'Mes cours')
@section('page-title', 'Supports de cours')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-book"></i> Mes modules</h6>
                @if(isset($semestresPossibles))
                <form method="GET" action="{{ route('etudiant.cours.index') }}" class="d-inline">
                    <select name="semestre" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        @foreach($semestresPossibles as $s)
                            <option value="{{ $s }}" {{ $semestreChoisi == $s ? 'selected' : '' }}>Semestre {{ $s }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>
            <div class="card-body">
                @if($modules->isEmpty())
                    <p class="text-muted">Aucun module pour ce semestre.</p>
                @else
                    <div class="list-group">
                        @foreach($modules as $module)
                            <a href="{{ route('etudiant.cours.index', ['module_id' => $module->id, 'semestre' => $semestreChoisi]) }}"
                               class="list-group-item list-group-item-action {{ $moduleId == $module->id ? 'active' : '' }}">
                                {{ $module->nom }}
                                <span class="badge bg-secondary float-end">{{ $module->supportCours->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if($moduleId)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-folder2"></i> Supports du module</h6>
                </div>
                <div class="card-body p-0">
                    @if($supports->isEmpty())
                        <div class="alert alert-light m-3">Aucun support disponible pour ce module.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supports as $support)
                                    <tr>
                                        <td>{{ $support->titre }}</td>
                                        <td>
                                            <a href="{{ route('etudiant.cours.telecharger', $support->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Télécharger
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="alert alert-info">Sélectionnez un module pour voir les supports.</div>
        @endif
    </div>
</div>
@endsection