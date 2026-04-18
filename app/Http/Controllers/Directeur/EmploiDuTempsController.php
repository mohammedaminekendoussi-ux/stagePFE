<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Seance;
use Illuminate\Http\Request;

class EmploiDuTempsController extends Controller
{
    const CRENEAUX = [
        '08:30-10:30',
        '10:30-12:30',
        '14:30-16:30',
        '16:30-18:30',
    ];

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    public function index(Request $request)
{
    $filieres = Filiere::all();
    $groupes  = collect();
    $emploi   = [];
    $groupe   = null;

    // Récupérer les groupes si une filière est sélectionnée
    if ($request->filled('filiere_id')) {
        $groupes = Groupe::where('filiere_id', $request->filiere_id)->get();
    }

    // Ne construire l'emploi du temps que si un groupe est explicitement sélectionné
    if ($request->filled('groupe_id')) {
        $groupe = Groupe::with('filiere')->find($request->groupe_id);
        if ($groupe) {
            $seances = Seance::with(['module', 'formateur'])
                ->where('groupe_id', $groupe->id)
                ->get();

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
    }

    return view('directeur.emploi', compact('filieres', 'groupes', 'emploi', 'groupe'));
}
}