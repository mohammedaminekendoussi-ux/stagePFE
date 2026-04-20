@extends('layouts.formateur')

@section('title', 'Appel - ' . $seance->module->nom)
@section('page-title', 'Appel des étudiants')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-person-check"></i> 
            {{ $seance->module->nom }} - Groupe {{ $seance->groupe->nom }}
            ({{ \Carbon\Carbon::parse($seance->jour)->format('d/m/Y') }} de {{ \Carbon\Carbon::parse($seance->h_debut)->format('H:i') }} à {{ \Carbon\Carbon::parse($seance->h_fin)->format('H:i') }})
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('formateur.absences.store', $seance->id) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Étudiant</th>
                            <th>Présent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($etudiants as $etudiant)
                        <tr>
                            <td>{{ $etudiant->prenom }} {{ $etudiant->nom }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="present[]" value="{{ $etudiant->id }}"
                                           class="form-check-input" id="present{{ $etudiant->id }}"
                                           {{ in_array($etudiant->id, $presentIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="present{{ $etudiant->id }}">Présent</label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('formateur.absences.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection