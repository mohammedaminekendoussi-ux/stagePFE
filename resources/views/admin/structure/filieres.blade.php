{{-- Bouton ajouter + Modal --}}
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddFiliere">
        <i class="bi bi-plus-circle"></i> Ajouter une filière
    </button>
</div>

{{-- Liste filières --}}
<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-diagram-3 text-primary"></i>
            Liste des filières
            <span class="badge bg-primary ms-2">{{ $filieres->count() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th class="text-center">Groupes</th>
                    <th class="text-center">Modules</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filieres as $filiere)
                <tr>
                    <td class="ps-4 text-muted">{{ $filiere->id }}</td>
                    <td class="fw-semibold">{{ $filiere->nom }}</td>
                    <td class="text-muted">{{ $filiere->description ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $filiere->groupes_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">{{ $filiere->modules_count }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            {{-- Modifier --}}
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditFiliere{{ $filiere->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            {{-- Supprimer --}}
                            <form method="POST"
                                  action="{{ route('admin.structure.filieres.destroy', $filiere->id) }}"
                                  onsubmit="return confirm('Supprimer cette filière ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Modal Modifier Filière --}}
                <div class="modal fade" id="modalEditFiliere{{ $filiere->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Modifier la filière</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.structure.filieres.update', $filiere->id) }}">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control"
                                               value="{{ $filiere->nom }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="3">{{ $filiere->description }}</textarea>
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
                        <i class="bi bi-diagram-3" style="font-size:2rem"></i>
                        <div class="mt-2">Aucune filière trouvée</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Ajouter Filière --}}
<div class="modal fade" id="modalAddFiliere" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-primary"></i> Nouvelle filière
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.structure.filieres.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: Génie Informatique" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Description de la filière..."></textarea>
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