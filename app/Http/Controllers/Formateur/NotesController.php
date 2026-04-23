<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\User;
use App\Models\Note;

class NotesController extends Controller
{
    private function getSemestresPossibles($annee)
    {
        switch ($annee) {
            case 1: return [1, 2];
            case 2: return [3, 4];
            case 3: return [5, 6];
            default: return [1, 2];
        }
    }

    private function getSemestreParAnneeEtDate($annee)
    {
        $semestresPossibles = $this->getSemestresPossibles($annee);
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
        return $semestresPossibles[0] ?? 1;
    }

    public function index(Request $request)
    {
        $formateur = auth()->user();
        $filiereId = $request->get('filiere_id');
        $groupeId = $request->get('groupe_id');
        $moduleId = $request->get('module_id');

        // Toutes les filières où le formateur a des modules
        $allFilieres = Filiere::whereHas('modules', function($q) use ($formateur) {
            $q->where('formateur_id', $formateur->id);
        })->get();

        // Groupes de la filière sélectionnée
        $groupes = collect();
        if ($filiereId) {
            $groupes = Groupe::where('filiere_id', $filiereId)->get();
        }

        // Modules : filtre par filière, par semestre automatique et par séances existantes
        $modules = collect();
        if ($filiereId) {
            $query = Module::where('formateur_id', $formateur->id)
                ->where('filiere_id', $filiereId);
            
            if ($groupeId) {
                $groupe = Groupe::find($groupeId);
                if ($groupe) {
                    $semestreActuel = $this->getSemestreParAnneeEtDate($groupe->annee);
                    $query->where('semestre', $semestreActuel)
                          ->whereHas('seances', function($q) use ($groupeId) {
                              $q->where('groupe_id', $groupeId);
                          });
                }
            }
            $modules = $query->get();
        }

        $etudiants = collect();
        $notes = [];

        if ($moduleId && $groupeId) {
            $module = Module::where('id', $moduleId)->where('formateur_id', $formateur->id)->first();
            if ($module) {
                $etudiants = User::where('role', 'etudiant')
                    ->where('groupe_id', $groupeId)
                    ->orderBy('nom')
                    ->get();
                foreach ($etudiants as $etudiant) {
                    $note = Note::where('module_id', $moduleId)
                        ->where('etudiant_id', $etudiant->id)
                        ->first();
                    $notes[$etudiant->id] = $note;
                }
            }
        }

        return view('formateur.notes.index', compact('allFilieres', 'groupes', 'modules', 'etudiants', 'notes', 'filiereId', 'groupeId', 'moduleId'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'groupe_id' => 'required|exists:groupes,id',
            'notes' => 'array',
            'notes.*.controle_continu' => 'nullable|numeric|min:0|max:20',
            'notes.*.examen_finale' => 'nullable|numeric|min:0|max:20',
        ]);

        $formateur = auth()->user();
        $module = Module::findOrFail($request->module_id);
        if ($module->formateur_id != $formateur->id) abort(403);

        foreach ($request->notes as $etudiantId => $noteData) {
            Note::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'etudiant_id' => $etudiantId,
                ],
                [
                    'controle_continu' => $noteData['controle_continu'] ?? null,
                    'examen_finale' => $noteData['examen_finale'] ?? null,
                ]
            );
        }

        return redirect()->route('formateur.notes.index', [
            'filiere_id' => $request->filiere_id,
            'groupe_id' => $request->groupe_id,
            'module_id' => $module->id,
        ])->with('success', 'Notes sauvegardées.');
    }

    public function validateNotes(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'groupe_id' => 'required|exists:groupes,id',
        ]);

        $formateur = auth()->user();
        $module = Module::findOrFail($request->module_id);
        if ($module->formateur_id != $formateur->id) abort(403);

        $etudiants = User::where('role', 'etudiant')->where('groupe_id', $request->groupe_id)->get();
        foreach ($etudiants as $etudiant) {
            $note = Note::where('module_id', $module->id)->where('etudiant_id', $etudiant->id)->first();
            if ($note) {
                $note->validee = true;
                $note->save();
            }
        }

        return redirect()->route('formateur.notes.index', [
            'filiere_id' => $request->filiere_id,
            'groupe_id' => $request->groupe_id,
            'module_id' => $module->id,
        ])->with('success', 'Notes validées.');
    }
}