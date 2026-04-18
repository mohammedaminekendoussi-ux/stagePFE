@extends('layouts.directeur')

@section('title', 'Emplois du temps')
@section('page-title', 'Consultation des emplois du temps')

@section('content')
<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar3 text-primary me-2"></i>
            Sélection du groupe
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('directeur.emploi.index') }}" id="formEmploi">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Filière</label>
                    <select name="filiere_id" id="filiere_id" class="form-select" required>
                        <option value="">-- Choisir une filière --</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}" {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>
                                {{ $filiere->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Groupe</label>
                    <select name="groupe_id" id="groupe_id" class="form-select" required>
                        <option value="">-- Choisir un groupe --</option>
                        @foreach($groupes as $groupe)
                            <option value="{{ $groupe->id }}" {{ request('groupe_id') == $groupe->id ? 'selected' : '' }}>
                                {{ $groupe->nom }}
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
                    <a href="{{ route('directeur.emploi.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if($groupe && request()->has('groupe_id'))
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar3 text-primary"></i>
            Emploi du temps — {{ $groupe->nom }} ({{ $groupe->filiere->nom }})
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 text-center align-middle" style="min-width:900px;">
                <thead>
                    <tr class="table-light">
                        <th class="fw-bold" style="width:130px;">Jour / Horaire</th>
                        @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                            <th class="fw-bold">{{ str_replace('-', ' - ', $creneau) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'] as $jour)
                    <tr>
                        <td class="fw-bold bg-light">{{ $jour }}</td>
                        @foreach(['08:30-10:30', '10:30-12:30', '14:30-16:30', '16:30-18:30'] as $creneau)
                            <td class="p-2">
                                @if(isset($emploi[$jour][$creneau]) && $emploi[$jour][$creneau])
                                    @php $seance = $emploi[$jour][$creneau]; @endphp
                                    <div class="rounded p-2 text-white" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                                        <div class="fw-bold small">{{ $seance->module->nom }}</div>
                                        <div class="small opacity-75">
                                            {{ $seance->formateur->prenom }} {{ $seance->formateur->nom }}
                                        </div>
                                        <div class="small opacity-75">
                                            <i class="bi bi-geo-alt"></i> {{ $seance->salle }}
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted">—</div>
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
    const filiereSelect = document.getElementById('filiere_id');
    const groupeSelect = document.getElementById('groupe_id');

    filiereSelect.addEventListener('change', function() {
        const filiereId = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('filiere_id', filiereId);
        url.searchParams.delete('groupe_id');
        window.location.href = url.toString();
    });
</script>
@endsection