<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SupportCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CoursController extends Controller
{
    public function index(Request $request)
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        if (!$groupe) {
            return redirect()->route('etudiant.dashboard')->with('error', 'Vous n\'êtes affecté à aucun groupe.');
        }

        // Récupérer les modules liés au groupe (via les séances)
        $modules = Module::whereHas('seances', function ($q) use ($groupe) {
            $q->where('groupe_id', $groupe->id);
        })->get();

        $moduleId = $request->get('module_id');
        $supports = [];

        if ($moduleId) {
            $supports = SupportCours::where('module_id', $moduleId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('etudiant.cours.index', compact('modules', 'moduleId', 'supports'));
    }

    public function telecharger($id)
    {
        $support = SupportCours::findOrFail($id);
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        // Vérifier que le module du support est bien dans le groupe de l'étudiant
        $module = $support->module;
        $moduleDansGroupe = Module::where('id', $module->id)
            ->whereHas('seances', fn($q) => $q->where('groupe_id', $groupe->id))
            ->exists();

        if (!$moduleDansGroupe) {
            abort(403, 'Vous n\'avez pas accès à ce document.');
        }

        $filePath = storage_path('app/public/' . $support->fichier);
        if (!file_exists($filePath)) {
            abort(404, 'Fichier introuvable.');
        }

        return response()->download($filePath, $support->titre . '.' . pathinfo($support->fichier, PATHINFO_EXTENSION));
    }
}