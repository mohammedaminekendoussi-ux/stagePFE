@extends('layouts.directeur')

@section('title', 'Dossiers des étudiants et formateurs')
@section('page-title', 'Consultation des dossiers')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $type == 'etudiants' ? 'active' : '' }}" 
                   href="{{ route('directeur.dossiers.index', array_merge(['type' => 'etudiants'], request()->except('type'))) }}">
                    <i class="bi bi-people"></i> Étudiants
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $type == 'formateurs' ? 'active' : '' }}" 
                   href="{{ route('directeur.dossiers.index', array_merge(['type' => 'formateurs'], request()->except('type'))) }}">
                    <i class="bi bi-person-badge"></i> Formateurs
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        @if($type == 'etudiants')
            <!-- Formulaire et tableau étudiants -->
            <form method="GET" action="{{ route('directeur.dossiers.index') }}" class="mb-4">
                <input type="hidden" name="type" value="etudiants">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Recherche</label>
                        <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filière</label>
                        <select name="filiere_id" class="form-select">
                            <option value="">Toutes</option>
                            @foreach($filieres as $filiere)
                                <option value="{{ $filiere->id }}" {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>{{ $filiere->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('directeur.dossiers.index', ['type' => 'etudiants']) }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>Nom complet</th><th>Email</th><th>Filière</th><th>Groupe</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($etudiants as $etudiant)
                        <tr>
                            <td>{{ $etudiant->id }}</td>
                            <td>{{ $etudiant->prenom }} {{ $etudiant->nom }}</td>
                            <td>{{ $etudiant->email }}</td>
                            <td>{{ $etudiant->groupe->filiere->nom ?? '-' }}</td>
                            <td>{{ $etudiant->groupe->nom ?? '-' }}</td>
                            <td>{!! $etudiant->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Désactivé</span>' !!}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">Aucun étudiant trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Formulaire et tableau formateurs -->
            <form method="GET" action="{{ route('directeur.dossiers.index') }}" class="mb-4">
                <input type="hidden" name="type" value="formateurs">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Recherche</label>
                        <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('directeur.dossiers.index', ['type' => 'formateurs']) }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formateurs as $formateur)
                        <tr>
                            <td>{{ $formateur->id }}</td>
                            <td>{{ $formateur->prenom }} {{ $formateur->nom }}</td>
                            <td>{{ $formateur->email }}</td>
                            <td>{!! $formateur->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Désactivé</span>' !!}</td>
                            <td>
                                <a href="{{ route('directeur.dossiers.formateur.absences', $formateur->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-calendar-check"></i> Séances
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Aucun formateur trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection