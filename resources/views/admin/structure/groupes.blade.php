{{-- Filtrage + Bouton ajouter --}}
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.structure.groupes') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Filtrer par filière</label>
                <select name="filiere_id" class="form-select">
                    <option value="">Toutes les filières</option>
                    @foreach($filieres as $filiere)
                        <option value="{{ $filiere->id }}"
                            {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>
                            {{ $filiere->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.structure.groupes') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
            <div class="col-md-4 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddGroupe">
        <i class="bi bi-plus-circle"></i> Ajouter un groupe
    </button>
</div>
        </div>
    </form>
</div>

{{-- Liste groupes --}}
<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-people text-primary"></i>
            Liste des groupes
            <span class="badge bg-primary ms-2">{{ $groupes->count() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Nom</th>
                    <th>Filière</th>
                    <th class="text-center">Année</th>
                    <th class="text-center">Étudiants</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupes as $groupe)
                <tr>
                    <td class="ps-4 text-muted">{{ $groupe->id }}</td>
                    <td class="fw-semibold">{{ $groupe->nom }}</td>
                    <td>
                        <span class="badge" style="background:#667eea">
                            {{ $groupe->filiere->nom ?? '—' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $groupe->annee }}</td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $groupe->etudiants->count() }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditGroupe{{ $groupe->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST"
                                  action="{{ route('admin.structure.groupes.destroy', $groupe->id) }}"
                                  onsubmit="return confirm('Supprimer ce groupe ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Modal Modifier Groupe --}}
                <div class="modal fade" id="modalEditGroupe{{ $groupe->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Modifier le groupe</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.structure.groupes.update', $groupe->id) }}">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control"
                                               value="{{ $groupe->nom }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                                        <select name="annee" class="form-select" required>
                                            @for($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ $groupe->annee == $i ? 'selected' : '' }}>
                                                    Année {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Filière <span class="text-danger">*</span></label>
                                        <select name="filiere_id" class="form-select" required>
                                            @foreach($filieres as $filiere)
                                                <option value="{{ $filiere->id }}"
                                                    {{ $groupe->filiere_id == $filiere->id ? 'selected' : '' }}>
                                                    {{ $filiere->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-people" style="font-size:2rem"></i>
                        <div class="mt-2">Aucun groupe trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Ajouter Groupe --}}
<div class="modal fade" id="modalAddGroupe" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-primary"></i> Nouveau groupe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.structure.groupes.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: G1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                        <select name="annee" class="form-select" required>
                            <option value="">-- Choisir une année --</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">Année {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filière <span class="text-danger">*</span></label>
                        <select name="filiere_id" class="form-select" required>
                            <option value="">-- Choisir une filière --</option>
                            @foreach($filieres as $filiere)
                                <option value="{{ $filiere->id }}">{{ $filiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>