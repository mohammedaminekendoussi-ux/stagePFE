@extends('layouts.formateur')

@section('title', 'Mon planning')
@section('page-title', 'Mon emploi du temps')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar-week text-primary"></i>
            Mes séances (tous groupes)
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
                                    <div class="rounded p-2 text-white" style="background:linear-gradient(135deg,#28a745,#20c997);">
                                        <div class="fw-bold small">{{ $seance->module->nom }}</div>
                                        <div class="small opacity-75">
                                            <i class="bi bi-people"></i> {{ $seance->groupe->nom }}
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
@endsection