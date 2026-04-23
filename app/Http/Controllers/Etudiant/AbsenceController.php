<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    private function getSemestresParAnnee($annee)
    {
        switch ($annee) {
            case 1: return [1, 2];
            case 2: return [3, 4];
            case 3: return [5, 6];
            default: return [1, 2];
        }
    }

    private function getSemestreActuel($semestresPossibles)
    {
        $mois = now()->month;
        // Septembre à février = semestre impair (1,3,5)
        $isImpair = ($mois >= 9 || $mois <= 2);
        if ($isImpair) {
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 1) return $s;
            }
        } else {
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 0) return $s;
            }
        }
        return $semestresPossibles[0];
    }

    public function index(Request $request)
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        if (!$groupe) {
            return view('etudiant.absences', ['absencesData' => collect()]);
        }

        $annee = $groupe->annee;
        $semestresPossibles = $this->getSemestresParAnnee($annee);
        $semestreChoisi = $request->get('semestre');
        if (!$semestreChoisi) {
            $semestreChoisi = $this->getSemestreActuel($semestresPossibles);
        }

        // Récupérer les modules du groupe, filtrés par semestre choisi
        $modules = Module::whereHas('seances', function ($q) use ($groupe) {
                $q->where('groupe_id', $groupe->id);
            })
            ->where('semestre', $semestreChoisi)
            ->get();

        $absencesData = [];

        foreach ($modules as $module) {
            $seancesTheoriques = ceil($module->volume_horaire / 2);
            $absences = Absence::where('etudiant_id', $etudiant->id)
                ->whereHas('seance', function ($q) use ($module, $groupe) {
                    $q->where('module_id', $module->id)
                      ->where('groupe_id', $groupe->id);
                })
                ->orderBy('date', 'desc')
                ->get();

            $nbAbsences = $absences->count();
            $tauxAbsence = $seancesTheoriques > 0 ? round(($nbAbsences / $seancesTheoriques) * 100, 2) : 0;
            $seuilCritique = 20;

            $absencesData[] = (object) [
                'module' => $module,
                'seances_theoriques' => $seancesTheoriques,
                'nb_absences' => $nbAbsences,
                'taux_absence' => $tauxAbsence,
                'seuil_depasse' => $tauxAbsence >= $seuilCritique,
                'absences' => $absences,
            ];
        }

        return view('etudiant.absences', compact('absencesData', 'semestresPossibles', 'semestreChoisi'));
    }
}