@extends('layouts.directeur')

@section('title', 'Présences du formateur')
@section('page-title', 'Présences du formateur : ' . $formateur->prenom . ' ' . $formateur->nom)

@section('content')
<div class="mb-3">
    <a href="{{ route('directeur.dossiers.index', ['type' => 'formateurs']) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste des formateurs
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar-check"></i> Séances et dates de présence
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Module</th>
                        <th>Groupe</th>
                        <th>Jour</th>
                        <th>Horaire</th>
                        <th>Dates de présence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seancesAvecDates as $item)
                    <tr>
                        <td>{{ $item['seance']->module->nom }}</td>
                        <td>{{ $item['seance']->groupe->nom }}</td>
                        <td>{{ $item['seance']->jour }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['seance']->h_debut)->format('H:i') }} - {{ \Carbon\Carbon::parse($item['seance']->h_fin)->format('H:i') }}</td>
                        <td>
                            @if($item['dates']->isEmpty())
                                <span class="text-muted">Aucune présence enregistrée</span>
                            @else
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($item['dates'] as $date)
                                        <span class="badge bg-success">{{ $date }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Aucune séance trouvée pour ce formateur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection