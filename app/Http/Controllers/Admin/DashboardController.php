<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Module;
use App\Models\Absence;
use App\Models\Seance;
use App\Models\Filiere;
use App\Models\Groupe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques existantes
        $totalEtudiants = User::where('role', 'etudiant')->count();
        $totalFormateurs = User::where('role', 'formateur')->where('actif', true)->count();
        $totalModules = Module::count();
        $absencesAujourdhui = Absence::whereDate('date', Carbon::today())->count();

        // Graphique absences 7 jours
        $absencesParJour = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d/m');
            $absencesParJour[] = Absence::whereDate('date', $date)->count();
        }

        // Graphique étudiants par filière
        $etudiantsParFiliere = User::where('role', 'etudiant')
            ->join('groupes', 'users.groupe_id', '=', 'groupes.id')
            ->join('filieres', 'groupes.filiere_id', '=', 'filieres.id')
            ->selectRaw('filieres.nom as filiere, COUNT(*) as total')
            ->groupBy('filieres.nom')
            ->get();

        // Alertes absences critiques
        $alertesAbsences = $this->getAlertesAbsences();

        return view('admin.dashboard', compact(
            'totalEtudiants',
            'totalFormateurs',
            'totalModules',
            'absencesAujourdhui',
            'absencesParJour',
            'labels',
            'etudiantsParFiliere',
            'alertesAbsences'
        ));
    }

    private function getAlertesAbsences()
{
    $alertes = [];
    $etudiants = User::where('role', 'etudiant')->with('groupe')->get();

    foreach ($etudiants as $etudiant) {
        // Récupérer tous les modules liés au groupe de l'étudiant (via les séances planifiées)
        $modules = Module::whereHas('seances', function ($q) use ($etudiant) {
            $q->where('groupe_id', $etudiant->groupe_id);
        })->get();

        foreach ($modules as $module) {
            // Calcul du nombre total de séances théoriques selon le volume horaire (2h par séance)
            $dureeSeance = 2; // heures
            $seancesTheoriques = ceil($module->volume_horaire / $dureeSeance);

            if ($seancesTheoriques == 0) continue;

            // Compter les absences réelles de l'étudiant pour ce module
            $absencesReelles = Absence::where('etudiant_id', $etudiant->id)
                ->whereHas('seance', function ($q) use ($module, $etudiant) {
                    $q->where('module_id', $module->id)
                      ->where('groupe_id', $etudiant->groupe_id);
                })
                ->count();

            $taux = ($absencesReelles / $seancesTheoriques) * 100;
            if ($taux > 20) {
                $alertes[] = [
                    'etudiant' => $etudiant,
                    'module' => $module,
                    'taux' => round($taux, 2),
                    'absencesReelles' => $absencesReelles,
                    'seancesTheoriques' => $seancesTheoriques,
                ];
            }
        }
    }

    return $alertes;
}

public function listAbsences($etudiantId, $moduleId)
{
    $absences = Absence::where('etudiant_id', $etudiantId)
        ->whereHas('seance', fn($q) => $q->where('module_id', $moduleId))
        ->get(['id', 'date', 'justifiee']);
    return response()->json($absences);
}

public function justifierAbsence(Request $request)
{
    $absence = Absence::findOrFail($request->id);
    $absence->justifiee = true;
    $absence->save();
    return response()->json(['success' => true]);
}
}