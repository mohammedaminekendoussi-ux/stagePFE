@extends('layouts.directeur')

@section('title', 'Tableau de bord Directeur')
@section('page-title', 'Indicateurs clés de l’établissement')

@section('content')

<!-- Cartes statistiques (style admin) -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 mb-1">Total Étudiants</div>
                    <div class="number">{{ $totalEtudiants ?? 0 }}</div>
                </div>
                <i class="bi bi-people-fill icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 mb-1">Taux présence global</div>
                    <div class="number">{{ $tauxPresenceGlobal ?? 0 }}%</div>
                </div>
                <i class="bi bi-graph-up icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 mb-1">Modules</div>
                    <div class="number">{{ $totalModules ?? 0 }}</div>
                </div>
                <i class="bi bi-book-fill icon"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 mb-1">Groupes</div>
                    <div class="number">{{ $totalGroupes ?? 0 }}</div>
                </div>
                <i class="bi bi-diagram-3-fill icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row g-4">
    <!-- Effectifs par filière (inchangé) -->
    <div class="col-xl-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-4">
                <i class="bi bi-pie-chart text-primary"></i>
                Effectifs par filière
            </h6>
            <canvas id="effectifsChart" height="200"></canvas>
        </div>
    </div>

    <!-- Modules les plus absents (inchangé) -->
    <div class="col-xl-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-4">
                <i class="bi bi-exclamation-triangle text-danger"></i>
                Modules les plus absents
            </h6>
            <canvas id="modulesAbsChart" height="200"></canvas>
        </div>
    </div>

    <!-- Taux de présence par groupe + filtre -->
    <div class="col-xl-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-graph-up text-success"></i>
                    Taux de présence par groupe
                </h6>
                <form method="GET" action="{{ route('directeur.dashboard') }}" class="d-flex gap-2">
                    <select name="filiere_id_presence" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">Toutes filières</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}" {{ request('filiere_id_presence') == $filiere->id ? 'selected' : '' }}>
                                {{ $filiere->nom }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('filiere_id_presence'))
                        <a href="{{ route('directeur.dashboard') }}" class="btn btn-sm btn-outline-secondary">×</a>
                    @endif
                </form>
            </div>
            <canvas id="presenceChart" height="200"></canvas>
        </div>
    </div>

    <!-- Moyennes générales par groupe + filtre -->
    <div class="col-xl-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-bar-chart-steps text-info"></i>
                    Moyennes générales par groupe
                </h6>
                <form method="GET" action="{{ route('directeur.dashboard') }}" class="d-flex gap-2">
                    <select name="filiere_id_moyenne" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">Toutes filières</option>
                        @foreach($filieres as $filiere)
                            <option value="{{ $filiere->id }}" {{ request('filiere_id_moyenne') == $filiere->id ? 'selected' : '' }}>
                                {{ $filiere->nom }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('filiere_id_moyenne'))
                        <a href="{{ route('directeur.dashboard') }}" class="btn btn-sm btn-outline-secondary">×</a>
                    @endif
                </form>
            </div>
            <canvas id="moyennesChart" height="200"></canvas>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Effectifs par filière
    const effectifsData = @json($effectifsParFiliere);
    const effectifsLabels = effectifsData.map(f => f.nom);
    const effectifsValues = effectifsData.map(f => f.etudiants_count);
    new Chart(document.getElementById('effectifsChart'), {
        type: 'bar',
        data: {
            labels: effectifsLabels,
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: effectifsValues,
                backgroundColor: 'rgba(102, 126, 234, 0.6)',
                borderColor: '#667eea',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, stepSize: 1 } }
        }
    });

    // 2. Taux de présence par groupe
    const groupesPresence = @json(array_keys($tauxPresenceParGroupe));
    const tauxPresence = @json(array_values($tauxPresenceParGroupe));
    new Chart(document.getElementById('presenceChart'), {
        type: 'bar',
        data: {
            labels: groupesPresence,
            datasets: [{
                label: 'Taux de présence (%)',
                data: tauxPresence,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: '#4facfe',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    // 3. Modules les plus absents
    const modulesLabels = @json($modulesImpact->pluck('nom'));
    const modulesAbs = @json($modulesImpact->pluck('total_absences'));
    new Chart(document.getElementById('modulesAbsChart'), {
        type: 'bar',
        data: {
            labels: modulesLabels,
            datasets: [{
                label: 'Nombre d\'absences',
                data: modulesAbs,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: '#f5576c',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // 4. Moyennes par groupe
    const groupesMoy = @json(array_keys($moyennesParGroupe));
    const moyennes = @json(array_values($moyennesParGroupe));
    new Chart(document.getElementById('moyennesChart'), {
        type: 'line',
        data: {
            labels: groupesMoy,
            datasets: [{
                label: 'Moyenne générale (/20)',
                data: moyennes,
                borderColor: '#43e97b',
                backgroundColor: 'rgba(67, 233, 123, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 20 } } }
    });
</script>
@endsection