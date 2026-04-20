@extends('layouts.formateur')

@section('title', 'Saisie des notes')
@section('page-title', 'Gestion des notes')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square"></i> Saisie des notes</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('formateur.notes.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filière</label>
                    <select name="filiere_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choisir --</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}" {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>{{ $filiere->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Groupe</label>
                    <select name="groupe_id" class="form-select">
                        <option value="">-- Choisir --</option>
                        @foreach($groupes as $groupe)
                            <option value="{{ $groupe->id }}" {{ request('groupe_id') == $groupe->id ? 'selected' : '' }}>{{ $groupe->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Module</label>
                    <select name="module_id" class="form-select">
                        <option value="">-- Choisir --</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>{{ $module->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Charger étudiants</button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('formateur.notes.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </div>
        </form>

        @if($etudiants->count() > 0 && $moduleId)
        <form method="POST" action="{{ route('formateur.notes.save') }}">
            @csrf
            <input type="hidden" name="module_id" value="{{ $moduleId }}">
            <input type="hidden" name="groupe_id" value="{{ request('groupe_id') }}">
            <input type="hidden" name="filiere_id" value="{{ request('filiere_id') }}">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Étudiant</th>
                            <th>Contrôle continu (0-20)</th>
                            <th>Examen final (0-20)</th>
                            <th>Moyenne (coefficient: {{ $module->coefficient ?? '?' }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($etudiants as $etudiant)
                        @php
                            $note = $notes[$etudiant->id] ?? null;
                            $cc = $note ? $note->controle_continu : '';
                            $exam = $note ? $note->examen_finale : '';
                            $moyenne = '';
                            if (is_numeric($cc) && is_numeric($exam)) {
                                $moyenne = round(($cc + $exam) / 2, 2);
                            }
                        @endphp
                        <tr>
                            <td>{{ $etudiant->prenom }} {{ $etudiant->nom }} ({{ $etudiant->email }})</td>
                            <td>
                                <input type="number" name="notes[{{ $etudiant->id }}][controle_continu]"
                                       class="form-control note-cc" step="0.25" min="0" max="20"
                                       value="{{ $cc }}" data-id="{{ $etudiant->id }}">
                            </td>
                            <td>
                                <input type="number" name="notes[{{ $etudiant->id }}][examen_finale]"
                                       class="form-control note-exam" step="0.25" min="0" max="20"
                                       value="{{ $exam }}" data-id="{{ $etudiant->id }}">
                            </td>
                            <td class="moyenne-cell" id="moyenne-{{ $etudiant->id }}">{{ $moyenne }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Sauvegarder les notes</button>
                <button type="button" id="btnValider" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalValider">Valider toutes les notes</button>
            </div>
        </form>

        <!-- Modal de confirmation pour validation -->
        <div class="modal fade" id="modalValider" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Validation des notes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Attention : une fois les notes validées, elles deviendront consultables par les étudiants. Vous ne pourrez plus les modifier ? (Selon votre politique, vous pouvez autoriser les modifications après validation, mais le cahier des charges dit que validation rend visible.)</p>
                        <p>Voulez-vous vraiment valider toutes les notes pour ce module et ce groupe ?</p>
                    </div>
                    <div class="modal-footer">
                        <form method="POST" action="{{ route('formateur.notes.validate') }}">
                            @csrf
                            <input type="hidden" name="module_id" value="{{ $moduleId }}">
                            <input type="hidden" name="groupe_id" value="{{ request('groupe_id') }}">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">Oui, valider</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Calcul automatique de la moyenne lors de la saisie
    document.querySelectorAll('.note-cc, .note-exam').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const ccInput = row.querySelector('.note-cc');
            const examInput = row.querySelector('.note-exam');
            const moyenneCell = row.querySelector('.moyenne-cell');
            let cc = parseFloat(ccInput.value);
            let exam = parseFloat(examInput.value);
            if (!isNaN(cc) && !isNaN(exam)) {
                let moyenne = (cc + exam) / 2;
                moyenneCell.textContent = moyenne.toFixed(2);
            } else {
                moyenneCell.textContent = '';
            }
        });
    });
</script>
@endpush
@endsection