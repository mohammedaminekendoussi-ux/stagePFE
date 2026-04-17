@extends('layouts.admin')

@section('title', 'Gestion Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')

    {{-- Message succès --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Barre de recherche + bouton ajouter --}}
    <div class="card p-4 mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Rechercher</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Nom, prénom ou email..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filtrer par rôle</label>
                    <select name="role" class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="administrateur" {{ request('role') == 'administrateur' ? 'selected' : '' }}>Administrateur</option>
                        <option value="directeur" {{ request('role') == 'directeur' ? 'selected' : '' }}>Directeur</option>
                        <option value="formateur" {{ request('role') == 'formateur' ? 'selected' : '' }}>Formateur</option>
                        <option value="etudiant" {{ request('role') == 'etudiant' ? 'selected' : '' }}>Étudiant</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tableau + bouton ajouter --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-people text-primary"></i>
                Liste des utilisateurs
                <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
            </h6>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Ajouter un utilisateur
            </a>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Groupe</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4 text-muted">{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:38px;height:38px;font-size:0.9rem;
                                     background:{{ $user->role === 'administrateur' ? '#667eea' : ($user->role === 'formateur' ? '#f5576c' : '#43e97b') }}">
                                    {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->prenom }} {{ $user->nom }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'administrateur')
    <span class="badge" style="background:#667eea">Administrateur</span>
@elseif($user->role === 'directeur')
    <span class="badge" style="background:#E67E22">Directeur</span>
@elseif($user->role === 'formateur')
    <span class="badge" style="background:#f5576c">Formateur</span>
@else
    <span class="badge" style="background:#43e97b;color:#1a1a1a">Étudiant</span>
@endif
                        </td>
                        <td class="text-muted">
                            {{ $user->groupe ? $user->groupe->nom : '—' }}
                        </td>
                        <td>
                            @if($user->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Désactivé</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                {{-- Modifier --}}
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Activer / Désactiver --}}
                                <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $user->actif ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $user->actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi {{ $user->actif ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>

                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                      onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x" style="font-size:2rem"></i>
                            <div class="mt-2">Aucun utilisateur trouvé</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

@endsection