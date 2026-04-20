@extends('layouts.etudiant')

@section('title', 'Supports de cours')
@section('page-title', 'Mes supports de cours')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-book"></i> Mes modules</h6>
            </div>
            <div class="card-body">
                @if($modules->isEmpty())
                    <p class="text-muted">Aucun module trouvé pour votre groupe.</p>
                @else
                    <div class="list-group">
                        @foreach($modules as $module)
                            <a href="{{ route('etudiant.cours.index', ['module_id' => $module->id]) }}"
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
                        <div class="alert alert-light m-3">Aucun support déposé pour ce module.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th>Taille</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supports as $support)
                                    <tr>
                                        <td>{{ $support->titre }}</td>
                                        <td>{{ $support->created_at->format('d/m/Y') }}</td>
                                        <td>{{ number_format($support->taille / 1024, 2) }} KB</td>
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
            <div class="alert alert-info">Sélectionnez un module pour voir ses supports.</div>
        @endif
    </div>
</div>
@endsection