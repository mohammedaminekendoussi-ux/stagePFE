@extends('layouts.etudiant')

@section('title', 'Mes notes')
@section('page-title', 'Mes notes par module')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark-fill text-primary"></i> Relevé de notes</h6>
    </div>
    <div class="card-body">
        @if(count($notesData) > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Module</th>
                            <th>Contrôle continu</th>
                            <th>Examen final</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notesData as $item)
                        <tr>
                            <td><strong>{{ $item->module->nom }}</strong></td>
                            <td>{{ $item->controle_continu ?? '-' }} /20</td>
                            <td>{{ $item->examen_finale ?? '-' }} /20</td>
                            <td>
                                @if($item->moyenne !== null)
                                    <span class="badge bg-primary">{{ $item->moyenne }} /20</span>
                                @else
                                    <span class="text-muted">Non saisie</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-4">
                <strong>Moyenne générale :</strong> {{ $moyenneGenerale }} /20
            </div>
        @else
            <div class="alert alert-warning">
                Aucun module trouvé pour votre groupe. Veuillez contacter l'administrateur.
            </div>
        @endif
    </div>
</div>
@endsection