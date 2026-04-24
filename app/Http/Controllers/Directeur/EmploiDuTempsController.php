<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Seance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmploiDuTempsController extends Controller
{
    const CRENEAUX = [
        '08:30-10:30',
        '10:30-12:30',
        '14:30-16:30',
        '16:30-18:30',
    ];

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

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
        $filieres = Filiere::all();
        $groupes = collect();
        $emploi = [];
        $groupe = null;
        $semestre = null;

        if ($request->filled('groupe_id')) {
            $groupe = Groupe::with('filiere')->findOrFail($request->groupe_id);
            $semestre = $this->getSemestreParAnneeEtDate($groupe->annee);
            
            $query = Seance::with(['module', 'formateur'])->where('groupe_id', $groupe->id);
            $query->whereHas('module', fn($q) => $q->where('semestre', $semestre));
            
            $seances = $query->get();

            foreach (self::JOURS as $jour) {
                foreach (self::CRENEAUX as $creneau) {
                    [$debut, $fin] = explode('-', $creneau);
                    $seance = $seances->first(function($s) use ($jour, $debut, $fin) {
                        return $s->jour === $jour
                            && $s->h_debut === $debut . ':00'
                            && $s->h_fin === $fin . ':00';
                    });
                    $emploi[$jour][$creneau] = $seance;
                }
            }
        }

        if ($request->filled('filiere_id')) {
            $groupes = Groupe::where('filiere_id', $request->filiere_id)->get();
        }

        return view('directeur.emploi', compact('filieres', 'groupes', 'emploi', 'groupe', 'semestre'));
    }
}