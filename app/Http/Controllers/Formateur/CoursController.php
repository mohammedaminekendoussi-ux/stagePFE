<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SupportCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoursController extends Controller
{
    private function getSemestreGroupeParDefaut()
    {
        $mois = now()->month;
        if ($mois >= 9 || $mois <= 2) {
            return 'impair';
        } else {
            return 'pair';
        }
    }

    public function index(Request $request)
    {
        $formateur = auth()->user();
        $groupeSemestre = $this->getSemestreGroupeParDefaut();
        $semestreChoisi = $request->get('semestre');

        // Récupérer les modules du formateur, filtrés par semestre si choisi
        $query = Module::where('formateur_id', $formateur->id);
        if ($semestreChoisi) {
            $query->where('semestre', $semestreChoisi);
        } else {
            // Par défaut, on limite aux semestres du groupe courant
            if ($groupeSemestre == 'impair') {
                $query->whereRaw('semestre % 2 = 1');
            } else {
                $query->whereRaw('semestre % 2 = 0');
            }
        }
        $modules = $query->get();

        $moduleId = $request->get('module_id');
        $supports = [];

        if ($moduleId) {
            $supports = SupportCours::where('module_id', $moduleId)
                ->where('formateur_id', $formateur->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('formateur.cours.index', compact('modules', 'moduleId', 'supports', 'groupeSemestre'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'titre' => 'required|string|max:255',
            'fichier' => 'required|file|mimes:pdf,docx,ppt,pptx|max:10240',
        ]);

        $formateur = auth()->user();
        $module = Module::findOrFail($request->module_id);

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

        return redirect()->route('formateur.cours.index', [
            'module_id' => $module->id,
            'semestre' => $request->semestre,
        ])->with('success', 'Support ajouté avec succès.');
    }

    public function destroy($id)
    {
        $support = SupportCours::findOrFail($id);
        $formateur = auth()->user();

        if ($support->formateur_id != $formateur->id) {
            abort(403);
        }

        Storage::disk('public')->delete($support->fichier);
        $support->delete();

        return redirect()->back()->with('success', 'Support supprimé.');
    }
}