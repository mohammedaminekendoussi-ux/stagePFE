<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SupportCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoursController extends Controller
{
    public function index(Request $request)
    {
        $formateur = auth()->user();
        $modules = Module::where('formateur_id', $formateur->id)->get();
        $moduleId = $request->get('module_id');
        $supports = [];

        if ($moduleId) {
            $supports = SupportCours::where('module_id', $moduleId)
                ->where('formateur_id', $formateur->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('formateur.cours.index', compact('modules', 'moduleId', 'supports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'titre' => 'required|string|max:255',
            'fichier' => 'required|file|mimes:pdf,docx,ppt,pptx|max:10240', // 10 Mo
        ]);

        $formateur = auth()->user();
        $module = Module::findOrFail($request->module_id);

        // Vérifier que le module appartient bien au formateur
        if ($module->formateur_id != $formateur->id) {
            abort(403, 'Module non autorisé');
        }

        $file = $request->file('fichier');
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $path = $file->storeAs('cours/' . $module->id, $fileName, 'public');

        SupportCours::create([
            'titre' => $request->titre,
            'taille' => $file->getSize(),
            'date_upload' => now(),
            'module_id' => $module->id,
            'formateur_id' => $formateur->id,
            'fichier' => $path,
        ]);

        return redirect()->route('formateur.cours.index', ['module_id' => $module->id])
                         ->with('success', 'Support ajouté avec succès.');
    }

    public function destroy($id)
    {
        $support = SupportCours::findOrFail($id);
        $formateur = auth()->user();

        // Vérifier que le support appartient au formateur
        if ($support->formateur_id != $formateur->id) {
            abort(403);
        }

        // Supprimer le fichier physique
        Storage::disk('public')->delete($support->fichier);
        $support->delete();

        return redirect()->back()->with('success', 'Support supprimé.');
    }
}