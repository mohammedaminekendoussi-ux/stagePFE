<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function index()
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        if (!$groupe) {
            return view('etudiant.notes', ['modules' => collect(), 'moyenneGenerale' => 0]);
        }

        // Récupérer tous les modules du groupe (via les séances)
        $modules = Module::whereHas('seances', function ($q) use ($groupe) {
            $q->where('groupe_id', $groupe->id);
        })->get();

        $notesData = [];
        $sommeNotes = 0;
        $nbModules = 0;

        foreach ($modules as $module) {
            $note = Note::where('module_id', $module->id)
                ->where('etudiant_id', $etudiant->id)
                ->first();

            $controle = $note ? $note->controle_continu : null;
            $examen = $note ? $note->examen_finale : null;
            $moyenne = null;
            if ($controle !== null && $examen !== null) {
                $moyenne = round(($controle + $examen) / 2, 2);
                $sommeNotes += $moyenne;
                $nbModules++;
            }

            $notesData[] = (object) [
                'module' => $module,
                'controle_continu' => $controle,
                'examen_finale' => $examen,
                'moyenne' => $moyenne,
            ];
        }

        $moyenneGenerale = $nbModules > 0 ? round($sommeNotes / $nbModules, 2) : 0;

        return view('etudiant.notes', compact('notesData', 'moyenneGenerale'));
    }
}