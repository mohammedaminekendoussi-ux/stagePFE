@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')

    <!-- Cartes statistiques -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Total Étudiants</div>
                        <div class="number">{{ $totalEtudiants }}</div>
                    </div>
                    <i class="bi bi-people-fill icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Formateurs Actifs</div>
                        <div class="number">{{ $totalFormateurs }}</div>
                    </div>
                    <i class="bi bi-person-badge-fill icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Modules en cours</div>
                        <div class="number">{{ $totalModules }}</div>
                    </div>
                    <i class="bi bi-book-fill icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 mb-1">Absences Aujourd'hui</div>
                        <div class="number">{{ $absencesAujourdhui }}</div>
                    </div>
                    <i class="bi bi-clipboard-x-fill icon"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Graphiques -->
    <div class="row g-4">

        <!-- Graphique absences 7 jours -->
        <div class="col-xl-8">
            <div class="card p-4">
                <h6 class="fw-bold mb-4">
                    <i class="bi bi-graph-up text-primary"></i>
                    Absences des 7 derniers jours
                </h6>
                <canvas id="absencesChart" height="100"></canvas>
            </div>
        </div>

        <!-- Graphique étudiants par filière -->
        <div class="col-xl-4">
            <div class="card p-4">
                <h6 class="fw-bold mb-4">
                    <i class="bi bi-pie-chart text-danger"></i>
                    Étudiants par filière
                </h6>
                <canvas id="filiereChart" height="200"></canvas>
            </div>
        </div>

        <!-- Sauvegardage BDD -->
        <div class="row mt-4">
    <div class="col-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold"><i class="bi bi-database"></i> Sauvegarde</h6>
                    <p class="text-muted mb-0">Générer un fichier .sql de toute la base de données</p>
                </div>
                <a href="{{ route('admin.backup.download') }}" 
                   class="btn btn-warning"
                   onclick="return confirm('Voulez-vous vraiment sauvegarder la base de données ?')">
                    <i class="bi bi-download"></i> Sauvegarder
                </a>
            </div>
        </div>
    </div>
</div>

    </div>

@endsection

@section('scripts')
<script>
    // Graphique absences 7 jours
    const ctx1 = document.getElementById('absencesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Absences',
                data: @json($absencesParJour),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#667eea',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Graphique étudiants par filière
    const ctx2 = document.getElementById('filiereChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: @json($etudiantsParFiliere->pluck('filiere')),
            datasets: [{
                data: @json($etudiantsParFiliere->pluck('total')),
                backgroundColor: ['#667eea', '#f5576c', '#4facfe', '#43e97b'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection