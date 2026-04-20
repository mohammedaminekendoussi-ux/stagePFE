<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Absence;
use App\Models\Seance;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    public function index()
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        if (!$groupe) {
            return view('etudiant.absences', ['modules' => collect()]);
        }

        // Récupérer les modules du groupe
        $modules = Module::whereHas('seances', function ($q) use ($groupe) {
            $q->where('groupe_id', $groupe->id);
        })->get();

        $absencesData = [];

        foreach ($modules as $module) {
            // Nombre de séances théoriques (volume_horaire / 2)
            $seancesTheoriques = ceil($module->volume_horaire / 2);

            // Absences réelles de l'étudiant pour ce module
            $absences = Absence::where('etudiant_id', $etudiant->id)
                ->whereHas('seance', function ($q) use ($module, $groupe) {
                    $q->where('module_id', $module->id)
                      ->where('groupe_id', $groupe->id);
                })
                ->orderBy('date', 'desc')
                ->get();

            $nbAbsences = $absences->count();
            $tauxAbsence = $seancesTheoriques > 0 ? round(($nbAbsences / $seancesTheoriques) * 100, 2) : 0;
            $seuilCritique = 20; // 20%

            $absencesData[] = (object) [
                'module' => $module,
                'seances_theoriques' => $seancesTheoriques,
                'nb_absences' => $nbAbsences,
                'taux_absence' => $tauxAbsence,
                'seuil_depasse' => $tauxAbsence >= $seuilCritique,
                'absences' => $absences,
            ];
        }

        return view('etudiant.absences', compact('absencesData'));
    }
}