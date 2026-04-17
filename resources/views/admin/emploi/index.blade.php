@extends('layouts.admin')

@section('title', 'Emploi du Temps')
@section('page-title', 'Gestion des Emplois du Temps')

@section('content')

    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Sélection filière + groupe --}}
    <div class="card p-4 mb-4">
        <form method="GET" action="{{ route('admin.emploi.index') }}" id="formSelection">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filière</label>
                    <select name="filiere_id" id="filiere_id" class="form-select" required>
                        <option value="">-- Choisir une filière --</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}"
                                {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>
                                {{ $filiere->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Groupe</label>
                    <select name="groupe_id" id="groupe_id" class="form-select" required>
    <option value="">-- Choisir un groupe --</option>
    @foreach($groupes as $g)
        <option value="{{ $g->id }}"
            {{ request('groupe_id') == $g->id ? 'selected' : '' }}>
            {{ $g->nom }}
        </option>
    @endforeach
</select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-eye"></i> Afficher
                    </button>
                </div>
                @if($groupe)
                <div class="col-md-2">
                    <a href="{{ route('admin.emploi.index', ['filiere_id' => request('filiere_id'), 'groupe_id' => request('groupe_id')]) }}"
                       class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Rafraîchir
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau emploi du temps --}}
    @if($groupe)
    <div class="card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-calendar3 text-primary"></i>
                Emploi du temps — {{ $groupe->nom }} {{ $groupe->filiere->nom }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="min-width:900px;">
                    <thead>
                        <tr class="table-light">
                            <th class="text-center fw-bold" style="width:130px;">Jour / Horaire</th>
                            @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                                <th class="text-center fw-bold">{{ str_replace('-', ' - ', $creneau) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'] as $jour)
                        <tr>
                            <td class="text-center fw-bold bg-light align-middle">{{ $jour }}</td>
                            @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                            <td class="text-center align-middle p-2" style="min-height:80px;">
                                @if(isset($emploi[$jour][$creneau]) && $emploi[$jour][$creneau])
                                    {{-- Case pleine --}}
                                    @php $seance = $emploi[$jour][$creneau]; @endphp
                                    <div class="rounded p-2 text-white"
                                         style="background:linear-gradient(135deg,#667eea,#764ba2);cursor:pointer;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#modalEdit{{ $seance->id }}">
                                        <div class="fw-bold small">{{ $seance->module->nom }}</div>
                                        <div class="small opacity-75">
                                            {{ $seance->formateur->prenom }} {{ $seance->formateur->nom }}
                                        </div>
                                        <div class="small opacity-75">
                                            <i class="bi bi-geo-alt"></i> {{ $seance->salle }}
                                        </div>
                                    </div>

                                    {{-- Modal Modifier --}}
                                    <div class="modal fade" id="modalEdit{{ $seance->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="bi bi-pencil text-primary"></i>
                                                        {{ $jour }} {{ str_replace('-', ' - ', $creneau) }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.emploi.update', $seance->id) }}">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="creneau" value="{{ $creneau }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Module</label>
                                                            <select name="module_id" class="form-select module-select"
                                                                    data-seance="{{ $seance->id }}" required>
                                                                @foreach(\App\Models\Module::where('filiere_id', $groupe->filiere_id)->get() as $module)
                                                                    <option value="{{ $module->id }}"
                                                                        {{ $seance->module_id == $module->id ? 'selected' : '' }}>
                                                                        {{ $module->nom }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Formateur</label>
                                                            <select name="formateur_id" class="form-select formateur-select-{{ $seance->id }}" required>
                                                                <option value="{{ $seance->formateur->id }}" selected>
                                                                    {{ $seance->formateur->prenom }} {{ $seance->formateur->nom }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
<select name="salle" class="form-select salle-edit-select"
        data-jour="{{ $seance->jour }}"
        data-creneau="{{ $creneau }}"
        data-exclude="{{ $seance->id }}" required>
    <option value="{{ $seance->salle }}" selected>{{ $seance->salle }}</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        {{-- Supprimer --}}
                                                        <form method="POST"
                                                              action="{{ route('admin.emploi.destroy', $seance->id) }}"
                                                              onsubmit="return confirm('Supprimer cette séance ?')"
                                                              class="me-auto">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="bi bi-trash"></i> Supprimer
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    {{-- Case vide --}}
                                    <button type="button" class="btn btn-light border w-100 py-3"
                                            style="color:#aaa;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAdd{{ $jour }}{{ str_replace(':', '', str_replace('-', '', $creneau)) }}">
                                        <i class="bi bi-plus-lg"></i> Ajouter
                                    </button>

                                    {{-- Modal Ajouter --}}
                                    <div class="modal fade" id="modalAdd{{ $jour }}{{ str_replace(':', '', str_replace('-', '', $creneau)) }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="bi bi-plus-circle text-primary"></i>
                                                        {{ $jour }} {{ str_replace('-', ' - ', $creneau) }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.emploi.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="groupe_id" value="{{ $groupe->id }}">
                                                    <input type="hidden" name="jour" value="{{ $jour }}">
                                                    <input type="hidden" name="creneau" value="{{ $creneau }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Module <span class="text-danger">*</span></label>
                                                            <select name="module_id" class="form-select module-add-select"
                                                                    data-jour="{{ $jour }}"
                                                                    data-creneau="{{ $creneau }}" required>
                                                                <option value="">-- Choisir un module --</option>
                                                                @foreach(\App\Models\Module::where('filiere_id', $groupe->filiere_id)->get() as $module)
                                                                    <option value="{{ $module->id }}">{{ $module->nom }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Formateur <span class="text-danger">*</span></label>
                                                            <select name="formateur_id"
                                                                    class="form-select formateur-add-{{ $jour }}{{ str_replace(':', '', str_replace('-', '', $creneau)) }}"
                                                                    required>
                                                                <option value="">-- Choisir d'abord un module --</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Salle <span class="text-danger">*</span></label>
<select name="salle" class="form-select salle-add-select"
        data-jour="{{ $jour }}"
        data-creneau="{{ $creneau }}" required>
    <option value="">-- Choisir d'abord un module --</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-check-circle"></i> Ajouter
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

@endsection

@section('scripts')
<script>
// Charger groupes quand filière change
document.getElementById('filiere_id').addEventListener('change', function() {
    const filiereId = this.value;
    if (filiereId) {
        const url = new URL(window.location.href);
        url.searchParams.set('filiere_id', filiereId);
        url.searchParams.delete('groupe_id');
        window.location.href = url.toString();
    }
});

// Fonction charger salles disponibles
function chargerSalles(selectSalle, jour, creneau, excludeId = null, salleActuelle = null) {
    let url = `/stagePFE/public/admin/emploi/salles?jour=${jour}&creneau=${creneau}`;
    if (excludeId) url += `&exclude_id=${excludeId}`;

    fetch(url)
        .then(r => r.json())
        .then(salles => {
            selectSalle.innerHTML = '<option value="">-- Choisir une salle --</option>';
            salles.forEach(s => {
                const option = document.createElement('option');
                option.value = s.nom;
                option.textContent = s.disponible ? s.nom : s.nom + ' (occupée)';
                option.disabled = !s.disponible;
                if (s.nom === salleActuelle) {
                    option.selected = true;
                    option.disabled = false;
                }
                selectSalle.appendChild(option);
            });
        });
}

// Charger formateurs + salles quand module change (modal ajouter)
document.querySelectorAll('.module-add-select').forEach(function(select) {
    select.addEventListener('change', function() {
        const moduleId = this.value;
        const jour = this.dataset.jour;
        const creneau = this.dataset.creneau;
        const key = jour + creneau.replace(/[:\-]/g, '');
        const formateurSelect = document.querySelector('.formateur-add-' + key);
        const salleSelect = document.querySelector(`.salle-add-select[data-jour="${jour}"][data-creneau="${creneau}"]`);

        if (!moduleId) {
            formateurSelect.innerHTML = '<option value="">-- Choisir d\'abord un module --</option>';
            salleSelect.innerHTML = '<option value="">-- Choisir d\'abord un module --</option>';
            return;
        }

        // Charger TOUS les formateurs disponibles (en passant jour+creneau)
        fetch(`/stagePFE/public/admin/emploi/formateurs/${moduleId}?jour=${jour}&creneau=${creneau}`)
            .then(r => r.json())
            .then(formateurs => {
                formateurSelect.innerHTML = '<option value="">-- Choisir un formateur --</option>';
                formateurs.forEach(f => {
                    const option = document.createElement('option');
                    option.value = f.id;
                    option.textContent = f.disponible
                        ? `${f.prenom} ${f.nom}`
                        : `${f.prenom} ${f.nom} (occupé)`;
                    option.disabled = !f.disponible;
                    formateurSelect.appendChild(option);
                });
            });

        // Charger les salles disponibles (ne change pas)
        chargerSalles(salleSelect, jour, creneau);
    });
});

// Charger les formateurs dans les modals d'édition quand ils s'ouvrent
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('show.bs.modal', function(event) {
        // On cherche le select des formateurs dans ce modal
        const formateurSelect = modal.querySelector('select[name="formateur_id"]');
        const salleSelect = modal.querySelector('select[name="salle"]');
        if (!formateurSelect) return;

        // Récupérer les infos du jour/creneau depuis les data-attributes du select de salle (ou autre)
        const jour = modal.querySelector('.salle-edit-select')?.dataset.jour;
        const creneau = modal.querySelector('.salle-edit-select')?.dataset.creneau;
        const excludeId = modal.querySelector('.salle-edit-select')?.dataset.exclude;
        const currentFormateurId = formateurSelect.value; // valeur actuelle

        if (jour && creneau) {
            fetch(`/stagePFE/public/admin/emploi/formateurs/0?jour=${jour}&creneau=${creneau}&exclude_id=${excludeId}`)
                .then(r => r.json())
                .then(formateurs => {
                    formateurSelect.innerHTML = '';
                    formateurs.forEach(f => {
                        const option = document.createElement('option');
                        option.value = f.id;
                        option.textContent = f.disponible
                            ? `${f.prenom} ${f.nom}`
                            : `${f.prenom} ${f.nom} (occupé)`;
                        option.disabled = !f.disponible;
                        if (f.id == currentFormateurId) {
                            option.selected = true;
                            option.disabled = false; // on force la sélection même si occupé
                        }
                        formateurSelect.appendChild(option);
                    });
                });
        }
    });
});

// Charger salles quand modal modifier s'ouvre
document.querySelectorAll('.salle-edit-select').forEach(function(select) {
    const jour = select.dataset.jour;
    const creneau = select.dataset.creneau;
    const excludeId = select.dataset.exclude;
    const salleActuelle = select.querySelector('option[selected]')?.value;
    chargerSalles(select, jour, creneau, excludeId, salleActuelle);
});
</script>
@endsection