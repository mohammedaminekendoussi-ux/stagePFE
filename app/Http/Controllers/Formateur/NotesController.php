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
    public function index(Request $request)
    {
        $formateur = auth()->user();

        // Filtrer les modules du formateur
        $modules = Module::where('formateur_id', $formateur->id)->get();
        $filieres = Filiere::all();
        $groupes = collect();
        $etudiants = collect();
        $moduleId = $request->get('module_id');
        $groupeId = $request->get('groupe_id');
        $notes = [];

        if ($moduleId && $groupeId) {
            // Vérifier que le module appartient bien au formateur
            $module = Module::where('id', $moduleId)->where('formateur_id', $formateur->id)->first();
            if ($module) {
                $groupes = Groupe::where('id', $groupeId)->get(); // ou tous les groupes de la filière
                $etudiants = User::where('role', 'etudiant')
                    ->where('groupe_id', $groupeId)
                    ->orderBy('nom')
                    ->get();
                // Récupérer les notes existantes pour ce module et ces étudiants
                foreach ($etudiants as $etudiant) {
                    $note = Note::where('module_id', $moduleId)
                        ->where('etudiant_id', $etudiant->id)
                        ->first();
                    $notes[$etudiant->id] = $note;
                }
            }
        }

        // Pour le select des groupes (dépendant de la filière)
        if ($request->filled('filiere_id')) {
            $groupes = Groupe::where('filiere_id', $request->filiere_id)->get();
        }

        return view('formateur.notes.index', compact('modules', 'filieres', 'groupes', 'etudiants', 'notes', 'moduleId', 'groupeId'));
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
        if ($module->formateur_id != $formateur->id) {
            abort(403);
        }

        foreach ($request->notes as $etudiantId => $noteData) {
            $note = Note::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'etudiant_id' => $etudiantId,
                ],
                [
                    'controle_continu' => $noteData['controle_continu'] ?? null,
                    'examen_finale' => $noteData['examen_finale'] ?? null,
                    // On ne change pas 'validee' ici, elle reste inchangée
                ]
            );
        }

        return redirect()->route('formateur.notes.index', [
            'module_id' => $module->id,
            'groupe_id' => $request->groupe_id,
            'filiere_id' => $request->filiere_id,
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
        if ($module->formateur_id != $formateur->id) {
            abort(403);
        }

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
        ])->with('success', 'Notes validées. Les étudiants peuvent maintenant les consulter.');
    }
}