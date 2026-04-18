@extends('layouts.directeur')

@section('title', 'Rapports détaillés')
@section('page-title', 'Rapports et analyses')

@section('content')
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-funnel-fill text-primary me-2"></i>
            Filtres personnalisés
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('directeur.rapports') }}" id="rapportForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Type de rapport</label>
                    <select name="type_rapport" class="form-select" required>
                        <option value="">-- Choisir --</option>
                        <option value="absences" {{ request('type_rapport') == 'absences' ? 'selected' : '' }}>📊 Absences</option>
                        <option value="notes" {{ request('type_rapport') == 'notes' ? 'selected' : '' }}>📈 Notes</option>
                        <option value="presence" {{ request('type_rapport') == 'presence' ? 'selected' : '' }}>✅ Taux de présence</option>
                        <option value="comparaison" {{ request('type_rapport') == 'comparaison' ? 'selected' : '' }}>📊 Comparaison entre deux filières</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filière</label>
                    <select name="filiere_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($filieres as $f)
                            <option value="{{ $f->id }}" {{ request('filiere_id') == $f->id ? 'selected' : '' }}>{{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Deuxième filière (pour comparaison)</label>
                    <select name="filiere_id2" class="form-select">
                        <option value="">-- Aucune --</option>
                        @foreach($filieres as $f)
                            <option value="{{ $f->id }}" {{ request('filiere_id2') == $f->id ? 'selected' : '' }}>{{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Groupe</label>
                    <select name="groupe_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($groupes as $g)
                            <option value="{{ $g->id }}" {{ request('groupe_id') == $g->id ? 'selected' : '' }}>{{ $g->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Formateur</label>
                    <select name="formateur_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($formateurs as $f)
                            <option value="{{ $f->id }}" {{ request('formateur_id') == $f->id ? 'selected' : '' }}>{{ $f->prenom }} {{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date fin</label>
                    <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Générer
                    </button>
                    @if(request('type_rapport'))
                    <a href="{{ route('directeur.rapports.export', request()->query()) }}" class="btn btn-success flex-fill">
                        <i class="bi bi-file-pdf"></i> Exporter PDF
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@if($rapportData)
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-table me-2 text-primary"></i>
            {{ $rapportData->title }}
        </h6>
        @if($rapportData->type != 'comparaison')
        <span class="badge bg-secondary rounded-pill">{{ count($rapportData->data) }} enregistrement(s)</span>
        @endif
    </div>
    <div class="card-body p-0">
        @if($rapportData->type == 'comparaison')
    @if(is_array($rapportData->data) && isset($rapportData->data['filiere1']))
        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Indicateur</th>
                            <th>{{ $rapportData->data['filiere1'] }}</th>
                            <th>{{ $rapportData->data['filiere2'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Effectif</strong></td>
                            <td>{{ $rapportData->data['effectif1'] }}</td>
                            <td>{{ $rapportData->data['effectif2'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Taux de présence</strong></td>
                            <td>{{ $rapportData->data['taux_presence1'] }}%</td>
                            <td>{{ $rapportData->data['taux_presence2'] }}%</td>
                        </tr>
                        <tr>
                            <td><strong>Total absences</strong></td>
                            <td>{{ $rapportData->data['total_absences1'] }}</td>
                            <td>{{ $rapportData->data['total_absences2'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Moyenne générale</strong></td>
                            <td>{{ $rapportData->data['moyenne1'] }}/20</td>
                            <td>{{ $rapportData->data['moyenne2'] }}/20</td>
                        </tr>
                        <tr>
                            <td><strong>Différence moyenne</strong></td>
                            <td colspan="2" class="text-center">{{ $rapportData->data['difference_moyenne'] }} points</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning m-3">{{ $rapportData->title ?? 'Données non disponibles' }}</div>
    @endif
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-uppercase small text-muted">
                            @if($rapportData->type == 'absences')
                                <th class="ps-3">Étudiant</th><th>Module</th><th>Groupe</th><th>Date</th><th>Justifiée</th>
                            @elseif($rapportData->type == 'notes')
                                <th class="ps-3">Étudiant</th><th>Module</th><th>Contrôle continu</th><th>Examen final</th><th>Moyenne</th>
                            @elseif($rapportData->type == 'presence')
                                <th>Groupe</th><th>Nb étudiants</th><th>Nb séances</th><th>Nb absences</th><th>Taux présence (%)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rapportData->data as $item)
                        <tr>
                            @if($rapportData->type == 'absences')
                                <td>{{ $item->etudiant->prenom }} {{ $item->etudiant->nom }}</td>
                                <td>{{ $item->seance->module->nom ?? '?' }}</td>
                                <td>{{ $item->seance->groupe->nom ?? '?' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                <td>{{ $item->justifiee ? 'Oui' : 'Non' }}</td>
                            @elseif($rapportData->type == 'notes')
                                <td>{{ $item->etudiant->prenom }} {{ $item->etudiant->nom }}</td>
                                <td>{{ $item->module->nom }}</td>
                                <td>{{ $item->controle_continu ?? '-' }}</td>
                                <td>{{ $item->examen_finale ?? '-' }}</td>
                                <td>{{ round(($item->controle_continu + $item->examen_finale)/2, 2) }}</td>
                            @elseif($rapportData->type == 'presence')
                                <td>{{ is_array($item) ? $item['groupe'] : $item->groupe }}</td>
                                <td>{{ is_array($item) ? $item['nb_etudiants'] : $item->nb_etudiants }}</td>
                                <td>{{ is_array($item) ? $item['nb_seances'] : $item->nb_seances }}</td>
                                <td>{{ is_array($item) ? $item['nb_absences'] : $item->nb_absences }}</td>
                                <td>{{ is_array($item) ? $item['taux_presence'] : $item->taux_presence }}%</td>
                            @endif
                        </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">Aucune donnée trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endsection