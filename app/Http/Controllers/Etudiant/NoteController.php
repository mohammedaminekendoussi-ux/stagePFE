<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
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
            return view('etudiant.notes', ['notesParSemestre' => [], 'moyennesParSemestre' => [], 'moyenneGenerale' => 0]);
        }

        $annee = $groupe->annee;
        $semestresPossibles = $this->getSemestresParAnnee($annee);
        $semestreActif = $request->get('semestre');
        if (!$semestreActif || !in_array($semestreActif, $semestresPossibles)) {
            $semestreActif = $this->getSemestreActuel($semestresPossibles);
        }

        // Récupérer tous les modules du groupe (pour tous semestres)
        $tousModules = Module::whereHas('seances', function ($q) use ($groupe) {
            $q->where('groupe_id', $groupe->id);
        })->get();

        // Organiser les notes par semestre (uniquement ceux possibles)
        $notesParSemestre = [];
        $moyennesParSemestre = [];
        $sommeNotesGenerale = 0;
        $nbModulesGeneraux = 0;

        foreach ($semestresPossibles as $semestre) {
            $modules = $tousModules->filter(fn($m) => $m->semestre == $semestre);
            if ($modules->isEmpty()) continue;

            $notesData = [];
            $sommeNotesSemestre = 0;
            $nbModulesSemestre = 0;

            foreach ($modules as $module) {
                $note = Note::where('module_id', $module->id)
                    ->where('etudiant_id', $etudiant->id)
                    ->where('validee', true)
                    ->first();

                $controle = $note ? $note->controle_continu : null;
                $examen = $note ? $note->examen_finale : null;
                $moyenne = null;
                if ($controle !== null && $examen !== null) {
                    $moyenne = round(($controle + $examen) / 2, 2);
                    $sommeNotesSemestre += $moyenne;
                    $nbModulesSemestre++;
                    $sommeNotesGenerale += $moyenne;
                    $nbModulesGeneraux++;
                }

                $notesData[] = (object) [
                    'module' => $module,
                    'controle_continu' => $controle,
                    'examen_finale' => $examen,
                    'moyenne' => $moyenne,
                ];
            }

            $notesParSemestre[$semestre] = $notesData;
            $moyennesParSemestre[$semestre] = $nbModulesSemestre > 0 ? round($sommeNotesSemestre / $nbModulesSemestre, 2) : 0;
        }

        $moyenneGenerale = $nbModulesGeneraux > 0 ? round($sommeNotesGenerale / $nbModulesGeneraux, 2) : 0;

        return view('etudiant.notes', compact('notesParSemestre', 'moyennesParSemestre', 'moyenneGenerale', 'semestresPossibles', 'semestreActif'));
    }
}