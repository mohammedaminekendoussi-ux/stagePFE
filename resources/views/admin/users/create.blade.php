@extends('layouts.admin')

@section('title', 'Ajouter Utilisateur')
@section('page-title', 'Ajouter un Utilisateur')

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person-plus text-primary"></i>
                    Nouveau compte utilisateur
                </h6>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control"
                                   value="{{ old('nom') }}" placeholder="Ex: Dupont" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control"
                                   value="{{ old('prenom') }}" placeholder="Ex: Jean" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email') }}" placeholder="Ex: jean@stage.com" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select" required>
    <option value="">-- Choisir un rôle --</option>
    <option value="administrateur" {{ old('role', $user->role ?? '') == 'administrateur' ? 'selected' : '' }}>Administrateur</option>
    <option value="directeur" {{ old('role', $user->role ?? '') == 'directeur' ? 'selected' : '' }}>Directeur</option>
    <option value="formateur" {{ old('role', $user->role ?? '') == 'formateur' ? 'selected' : '' }}>Formateur</option>
    <option value="etudiant" {{ old('role', $user->role ?? '') == 'etudiant' ? 'selected' : '' }}>Étudiant</option>
</select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mot de passe provisoire <span class="text-danger">*</span></label>
                            <input type="password" name="mot_de_passe" class="form-control"
                                   placeholder="Min. 6 caractères" required>
                        </div>

                        {{-- Groupe affiché seulement si étudiant --}}
                        <div class="col-12" id="groupe-field" style="display:none;">
                            <label class="form-label fw-semibold">
                                Groupe <span class="text-danger">*</span>
                            </label>
                            <select name="groupe_id" class="form-select">
                                <option value="">-- Choisir un groupe --</option>
                                @foreach($groupes as $groupe)
                                    <option value="{{ $groupe->id }}"
                                        {{ old('groupe_id') == $groupe->id ? 'selected' : '' }}>
                                        {{ $groupe->nom }} — {{ $groupe->filiere->nom ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Créer le compte
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x-circle"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Afficher le champ groupe seulement si rôle = étudiant
    const roleSelect = document.getElementById('role');
    const groupeField = document.getElementById('groupe-field');

    roleSelect.addEventListener('change', function() {
        groupeField.style.display = this.value === 'etudiant' ? 'block' : 'none';
    });

    // Au chargement si old('role') = etudiant
    if (roleSelect.value === 'etudiant') {
        groupeField.style.display = 'block';
    }
</script>
@endsection