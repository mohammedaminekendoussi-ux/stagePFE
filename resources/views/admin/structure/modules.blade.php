{{-- Filtrage + Bouton ajouter --}}
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.structure.modules') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label fw-semibold">Filtrer par formateur</label>
                <select name="formateur_id" class="form-select">
                    <option value="">Tous les formateurs</option>
                    @foreach($formateurs as $formateur)
                        <option value="{{ $formateur->id }}"
                            {{ request('formateur_id') == $formateur->id ? 'selected' : '' }}>
                            {{ $formateur->prenom }} {{ $formateur->nom }}
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
                <a href="{{ route('admin.structure.modules') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalAddModule">
    <i class="bi bi-plus-circle"></i> Ajouter
</button>
            </div>
        </div>
    </form>
</div>

{{-- Liste modules --}}
<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-book text-primary"></i>
            Liste des modules
            <span class="badge bg-primary ms-2">{{ $modules->count() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Nom</th>
                    <th>Filière</th>
                    <th>Formateur</th>
                    <th class="text-center">Coefficient</th>
                    <th class="text-center">Volume horaire</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modules as $module)
                <tr>
                    <td class="ps-4 text-muted">{{ $module->id }}</td>
                    <td class="fw-semibold">{{ $module->nom }}</td>
                    <td>
                        <span class="badge" style="background:#667eea">
                            {{ $module->filiere->nom ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($module->formateur)
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:32px;height:32px;font-size:0.8rem;background:#f5576c">
                                    {{ strtoupper(substr($module->formateur->prenom, 0, 1)) }}{{ strtoupper(substr($module->formateur->nom, 0, 1)) }}
                                </div>
                                {{ $module->formateur->prenom }} {{ $module->formateur->nom }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">{{ $module->coefficient }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $module->volume_horaire }}h</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditModule{{ $module->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST"
                                  action="{{ route('admin.structure.modules.destroy', $module->id) }}"
                                  onsubmit="return confirm('Supprimer ce module ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Modal Modifier Module --}}
                <div class="modal fade" id="modalEditModule{{ $module->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Modifier le module</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.structure.modules.update', $module->id) }}">
                                @csrf @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                        <input type="text" name="nom" class="form-control"
                                               value="{{ $module->nom }}" required>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Coefficient <span class="text-danger">*</span></label>
                                            <input type="number" name="coefficient" class="form-control"
                                                   value="{{ $module->coefficient }}" step="0.5" min="0.5" max="10" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Volume horaire (h) <span class="text-danger">*</span></label>
                                            <input type="number" name="volume_horaire" class="form-control"
                                                   value="{{ $module->volume_horaire }}" min="1" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Filière <span class="text-danger">*</span></label>
                                        <select name="filiere_id" class="form-select" required>
                                            @foreach($filieres as $filiere)
                                                <option value="{{ $filiere->id }}"
                                                    {{ $module->filiere_id == $filiere->id ? 'selected' : '' }}>
                                                    {{ $filiere->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Formateur <span class="text-danger">*</span></label>
                                        <select name="formateur_id" class="form-select" required>
                                            @foreach($formateurs as $formateur)
                                                <option value="{{ $formateur->id }}"
                                                    {{ $module->formateur_id == $formateur->id ? 'selected' : '' }}>
                                                    {{ $formateur->prenom }} {{ $formateur->nom }}
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-book" style="font-size:2rem"></i>
                        <div class="mt-2">Aucun module trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Ajouter Module --}}
<div class="modal fade" id="modalAddModule" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle text-primary"></i> Nouveau module
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.structure.modules.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: Mathématiques" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Coefficient <span class="text-danger">*</span></label>
                            <input type="number" name="coefficient" class="form-control"
                                   placeholder="Ex: 2.5" step="0.5" min="0.5" max="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Volume horaire (h) <span class="text-danger">*</span></label>
                            <input type="number" name="volume_horaire" class="form-control"
                                   placeholder="Ex: 40" min="1" required>
                        </div>
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
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Formateur <span class="text-danger">*</span></label>
                        <select name="formateur_id" class="form-select" required>
                            <option value="">-- Choisir un formateur --</option>
                            @foreach($formateurs as $formateur)
                                <option value="{{ $formateur->id }}">
                                    {{ $formateur->prenom }} {{ $formateur->nom }}
                                </option>
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