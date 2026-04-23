@extends('layouts.formateur')

@section('title', 'Mes cours')
@section('page-title', 'Gestion des supports de cours')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-book"></i> Mes modules</h6>
                {{-- Petit filtre semestre --}}
                <form method="GET" action="{{ route('formateur.cours.index') }}" class="d-inline">
                    <select name="semestre" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        @if($groupeSemestre == 'impair')
                            <option value="1" {{ request('semestre') == 1 ? 'selected' : '' }}>S1</option>
                            <option value="3" {{ request('semestre') == 3 ? 'selected' : '' }}>S3</option>
                            <option value="5" {{ request('semestre') == 5 ? 'selected' : '' }}>S5</option>
                        @else
                            <option value="2" {{ request('semestre') == 2 ? 'selected' : '' }}>S2</option>
                            <option value="4" {{ request('semestre') == 4 ? 'selected' : '' }}>S4</option>
                            <option value="6" {{ request('semestre') == 6 ? 'selected' : '' }}>S6</option>
                        @endif
                    </select>
                </form>
            </div>
            <div class="card-body">
                @if($modules->isEmpty())
                    <p class="text-muted">Aucun module assigné pour ce semestre.</p>
                @else
                    <div class="list-group">
                        @foreach($modules as $module)
                            <a href="{{ route('formateur.cours.index', ['module_id' => $module->id, 'semestre' => request('semestre')]) }}"
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-folder2"></i> Supports du module</h6>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutSupport">
                        <i class="bi bi-plus-circle"></i> Ajouter un support
                    </button>
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
                                        <th>Fichier</th>
                                        <th>Date</th>
                                        <th>Taille</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supports as $support)
                                    <tr>
                                        <td>{{ $support->titre }}</td>
                                        <td>
                                            <a href="{{ Storage::url($support->fichier) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Télécharger
                                            </a>
                                        </td>
                                        <td>{{ $support->date_upload->format('d/m/Y') }}</td>
                                        <td>{{ number_format($support->taille / 1024, 2) }} KB</td>
                                        <td>
                                            <form method="POST" action="{{ route('formateur.cours.destroy', $support->id) }}" onsubmit="return confirm('Supprimer ce support ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
            <div class="alert alert-info">Sélectionnez un module pour voir ou ajouter des supports.</div>
        @endif
    </div>
</div>

<!-- Modal Ajout Support -->
<div class="modal fade" id="modalAjoutSupport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Ajouter un support</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('formateur.cours.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module_id" value="{{ $moduleId }}">
                <input type="hidden" name="semestre" value="{{ request('semestre') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fichier (PDF, PPTX, DOCX, max 10 Mo)</label>
                        <input type="file" name="fichier" class="form-control" required accept=".pdf,.docx,.ppt,.pptx">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Uploader</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection