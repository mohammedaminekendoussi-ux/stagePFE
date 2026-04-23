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
    private function getSemestresParAnnee($annee)
    {
        // Selon l'année du groupe, retourne les deux numéros de semestre possibles
        switch ($annee) {
            case 1: return [1, 2];
            case 2: return [3, 4];
            case 3: return [5, 6];
            default: return [1, 2];
        }
    }

    private function getSemestreActuel($semestresPossibles)
    {
        // Détermine automatiquement quel semestre est en cours selon la date
        $mois = now()->month;
        // Semestre impair (automne/hiver) si mois entre septembre et février
        $isImpair = ($mois >= 9 || $mois <= 2);
        if ($isImpair) {
            // Choisir le semestre impair parmi les possibles
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 1) return $s;
            }
        } else {
            // Choisir le semestre pair
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 0) return $s;
            }
        }
        // Fallback: premier semestre de la liste
        return $semestresPossibles[0];
    }

    public function index(Request $request)
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        if (!$groupe) {
            return redirect()->route('etudiant.dashboard')->with('error', 'Vous n\'êtes affecté à aucun groupe.');
        }

        $annee = $groupe->annee; // 1,2,3
        $semestresPossibles = $this->getSemestresParAnnee($annee);
        $semestreChoisi = $request->get('semestre');
        if (!$semestreChoisi) {
            $semestreChoisi = $this->getSemestreActuel($semestresPossibles);
        }

        // Récupérer les modules du groupe, filtrés par semestre si choisi
        $query = Module::whereHas('seances', function ($q) use ($groupe) {
            $q->where('groupe_id', $groupe->id);
        });

        if ($semestreChoisi) {
            $query->where('semestre', $semestreChoisi);
        }

        $modules = $query->get();

        $moduleId = $request->get('module_id');
        $supports = [];

        if ($moduleId) {
            $supports = SupportCours::where('module_id', $moduleId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('etudiant.cours.index', compact('modules', 'moduleId', 'supports', 'semestresPossibles', 'semestreChoisi'));
    }

    public function telecharger($id)
    {
        $support = SupportCours::findOrFail($id);
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

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