<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    const CRENEAUX = [
        '08:30-10:30',
        '10:30-12:30',
        '14:30-16:30',
        '16:30-18:30',
    ];

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    private function getSemestreGroupeParDefaut()
    {
        $mois = now()->month;
        // Septembre (9) à Février (2) -> impairs, Mars (3) à Août (8) -> pairs
        if ($mois >= 9 || $mois < 2) {
            return 'impair';
        } else {
            return 'pair';
        }
    }

    public function index(Request $request)
    {
        $formateur = auth()->user();
        $groupeSemestre = $request->get('groupe_semestre');
        
        if (!$groupeSemestre) {
            $groupeSemestre = $this->getSemestreGroupeParDefaut();
        }

        // Récupérer les séances du formateur en filtrant par parité du semestre
        $seances = Seance::with(['module', 'groupe'])
            ->where('formateur_id', $formateur->id)
            ->whereHas('module', function ($q) use ($groupeSemestre) {
                if ($groupeSemestre == 'impair') {
                    $q->whereRaw('semestre % 2 = 1');
                } else {
                    $q->whereRaw('semestre % 2 = 0');
                }
            })
            ->get();

        // Construire le tableau emploi du temps
        $emploi = [];
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

        return view('formateur.emploi', compact('emploi', 'groupeSemestre'));
    }
}