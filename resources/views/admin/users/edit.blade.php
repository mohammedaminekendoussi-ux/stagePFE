@extends('layouts.admin')

@section('title', 'Modifier Utilisateur')
@section('page-title', 'Modifier un Utilisateur')

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-pencil text-primary"></i>
                    Modifier le compte de {{ $user->prenom }} {{ $user->nom }}
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

                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control"
                                   value="{{ old('nom', $user->nom) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control"
                                   value="{{ old('prenom', $user->prenom) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
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
                            <label class="form-label fw-semibold">
                                Nouveau mot de passe
                                <small class="text-muted fw-normal">(laisser vide pour ne pas changer)</small>
                            </label>
                            <input type="password" name="mot_de_passe" class="form-control"
                                   placeholder="Min. 6 caractères">
                        </div>

                        {{-- Groupe --}}
                        <div class="col-12" id="groupe-field"
                             style="display: {{ $user->role === 'etudiant' ? 'block' : 'none' }}">
                            <label class="form-label fw-semibold">Groupe</label>
                            <select name="groupe_id" class="form-select">
                                <option value="">-- Choisir un groupe --</option>
                                @foreach($groupes as $groupe)
                                    <option value="{{ $groupe->id }}"
                                        {{ $user->groupe_id == $groupe->id ? 'selected' : '' }}>
                                        {{ $groupe->nom }} — {{ $groupe->filiere->nom ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Statut --}}
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                       id="actif" name="actif" value="1"
                                       {{ $user->actif ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="actif">
                                    Compte actif
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle"></i> Enregistrer
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
    const roleSelect = document.getElementById('role');
    const groupeField = document.getElementById('groupe-field');

    roleSelect.addEventListener('change', function() {
        groupeField.style.display = this.value === 'etudiant' ? 'block' : 'none';
    });
</script>
@endsection