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
// Détermine automatiquement le groupe de semestres (impair/pair) selon la date
    private function getSemestreGroupeParDefaut()
    {
        $mois = now()->month;
        // Septembre (9) à Février (2) -> impairs (S1,S3,S5)
        // Mars (3) à Août (8) -> pairs (S2,S4,S6)
        if ($mois >= 9 || $mois <= 2) {
            return 'impair';
        } else {
            return 'pair';
        }
    }

    public function index(Request $request)
    {
        $formateur = auth()->user();

        // Groupe de semestre automatique
        $groupeSemestre = $this->getSemestreGroupeParDefaut();

        // Semestre spécifique choisi par l'utilisateur (1,2,3,4,5,6)
        $semestre = $request->get('semestre');

        // Construction de la requête des modules du formateur
        $query = Module::where('formateur_id', $formateur->id);

        // Filtrage par semestre spécifique (si choisi)
        if ($semestre) {
            $query->where('semestre', $semestre);
        } else {
            // Sinon, on filtre par groupe (impair/pair) pour limiter l'affichage par défaut
            if ($groupeSemestre == 'impair') {
                $query->whereRaw('semestre % 2 = 1');
            } else {
                $query->whereRaw('semestre % 2 = 0');
            }
        }

        $modules = $query->get();

        // Récupérer les filières concernées par ces modules
        $filiereIds = $modules->pluck('filiere_id')->unique();
        $filieres = Filiere::whereIn('id', $filiereIds)->get();

        // Groupes (filtrés par filière si demandé)
        $groupes = collect();
        if ($request->filled('filiere_id')) {
            $groupes = Groupe::where('filiere_id', $request->filiere_id)->get();
        }

        $moduleId = $request->get('module_id');
        $groupeId = $request->get('groupe_id');
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

        return view('formateur.notes.index', compact(
            'modules',
            'filieres',
            'groupes',
            'etudiants',
            'notes',
            'moduleId',
            'groupeId',
            'groupeSemestre'
        ));
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
            'module_id' => $module->id,
            'groupe_id' => $request->groupe_id,
            'filiere_id' => $request->filiere_id,
            'groupe_semestre' => $request->groupe_semestre,
            'semestre' => $request->semestre,
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
            'module_id' => $module->id,
            'groupe_id' => $request->groupe_id,
            'filiere_id' => $request->filiere_id,
            'groupe_semestre' => $request->groupe_semestre,
            'semestre' => $request->semestre,
        ])->with('success', 'Notes validées.');
    }
}