<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace étudiant') — StagePFE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2C3E50 0%, #1a252f 100%);
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding-top: 20px;
        }
        .sidebar .logo {
            color: white;
            font-size: 1.3rem;
            font-weight: bold;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .topbar {
            background: white;
            padding: 15px 30px;
            margin-left: 250px;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 99;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        .info-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="bi bi-mortarboard-fill"></i> StagePFE
        </div>
        <nav class="nav flex-column mt-2">
            <a href="{{ route('etudiant.dashboard') }}" class="nav-link {{ request()->routeIs('etudiant.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="{{ route('etudiant.cours.index') }}" class="nav-link">
    <i class="bi bi-folder2"></i> Supports de cours
</a>
            <a href="{{ route('etudiant.notes') }}" class="nav-link">
    <i class="bi bi-journal-bookmark-fill"></i> Mes notes
</a>
            <a href="{{ route('etudiant.absences') }}" class="nav-link">
    <i class="bi bi-clipboard-x"></i> Mes absences
</a>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="bi bi-box-arrow-left"></i> Déconnexion
                </button>
            </form>
        </nav>
    </div>

    <div class="topbar">
        <h5 class="mb-0 fw-bold">@yield('page-title', 'Espace étudiant')</h5>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted"><i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}</span>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:35px;height:35px;">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <span class="fw-semibold">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</span>
            </div>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>