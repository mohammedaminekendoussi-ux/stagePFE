@extends('layouts.formateur')

@section('title', 'Gestion des absences')
@section('page-title', 'Liste de présence')

@section('content')
@if($seance)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar-check"></i> Séance : {{ $seance->jour }} ({{ \Carbon\Carbon::parse($seance->h_debut)->format('H:i') }} - {{ \Carbon\Carbon::parse($seance->h_fin)->format('H:i') }})
            - Module : {{ $seance->module->nom }} - Groupe : {{ $seance->groupe->nom }}
        </h6>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#selectSeance">
            Choisir une autre séance
        </button>
    </div>
    <div class="collapse" id="selectSeance">
        <div class="card-body border-top">
            <form method="GET" action="{{ route('formateur.absences.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Sélectionner une séance</label>
                    <select name="seance_id" class="form-select">
                        <option value="">-- Choisir --</option>
                        @foreach($seances as $s)
                            <option value="{{ $s->id }}" {{ request('seance_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->jour }} {{ \Carbon\Carbon::parse($s->h_debut)->format('H:i') }} - {{ $s->module->nom }} ({{ $s->groupe->nom }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date de la séance</label>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Afficher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people"></i> Étudiants du groupe</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('formateur.absences.store', ['seanceId' => $seance->id]) }}">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="checkAll"> Tous</th>
                            <th>Étudiant</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($etudiants as $etudiant)
                        <tr>
                            <td>
                                <input type="checkbox" name="etudiants_presents[]" value="{{ $etudiant->id }}"
                                       class="etudiant-checkbox"
                                       {{ in_array($etudiant->id, $presences) ? 'checked' : '' }}>
                            </td>
                            <td>{{ $etudiant->prenom }} {{ $etudiant->nom }}</td>
                            <td>{{ $etudiant->email }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Enregistrer les présences
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.etudiant-checkbox');
        for (let cb of checkboxes) cb.checked = this.checked;
    });
</script>
@else
<div class="alert alert-warning">
    Aucune séance en cours. 
    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#selectSeanceManuel">Choisir une séance</button>
    <div class="collapse mt-2" id="selectSeanceManuel">
        <form method="GET" action="{{ route('formateur.absences.index') }}" class="row g-3">
            <div class="col-md-5">
                <select name="seance_id" class="form-select">
                    <option value="">-- Choisir une séance --</option>
                    @foreach($seances as $s)
                        <option value="{{ $s->id }}">{{ $s->jour }} {{ \Carbon\Carbon::parse($s->h_debut)->format('H:i') }} - {{ $s->module->nom }} ({{ $s->groupe->nom }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Afficher</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection