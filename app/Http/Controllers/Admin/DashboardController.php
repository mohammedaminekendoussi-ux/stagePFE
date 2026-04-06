<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Module;
use App\Models\Absence;
use App\Models\Seance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Nombre total d'étudiants
        $totalEtudiants = User::where('role', 'etudiant')->count();

        // Nombre de formateurs actifs
        $totalFormateurs = User::where('role', 'formateur')
                               ->where('actif', true)
                               ->count();

        // Nombre de modules
        $totalModules = Module::count();

        // Absences du jour
        $absencesAujourdhui = Absence::whereDate('date', Carbon::today())->count();

        // Graphique : absences des 7 derniers jours
        $absencesParJour = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            $absencesParJour[] = Absence::whereDate('date', $date)->count();
        }

        // Graphique : étudiants par filière
        $etudiantsParFiliere = User::where('role', 'etudiant')
            ->join('groupes', 'users.groupe_id', '=', 'groupes.id')
            ->join('filieres', 'groupes.filiere_id', '=', 'filieres.id')
            ->selectRaw('filieres.nom as filiere, COUNT(*) as total')
            ->groupBy('filieres.nom')
            ->get();

// ... autres stats
    $alertesAbsences = [];
    $etudiants = User::where('role', 'etudiant')->get();
    foreach ($etudiants as $etudiant) {
        $alertes = $etudiant->getTauxAbsenceParModule();
        if (!empty($alertes)) {
            $alertesAbsences = array_merge($alertesAbsences, $alertes);
        }
    }

        return view('admin.dashboard', compact(
            'alertesAbsences',
            'totalEtudiants',
            'totalFormateurs',
            'totalModules',
            'absencesAujourdhui',
            'absencesParJour',
            'labels',
            'etudiantsParFiliere'
        ));
    }
}