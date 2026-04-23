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

    // Détermine automatiquement le groupe de semestres (impair/pair) selon la date
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
        $filieres = Filiere::all();
        $groupes = collect();
        $emploi = [];
        $groupe = null;

        $groupeSemestre = $this->getSemestreGroupeParDefaut();
        $semestreChoisi = $request->get('semestre'); // 1,2,3,4,5,6 ou null

        if ($request->filled('groupe_id')) {
            $groupe = Groupe::with('filiere')->findOrFail($request->groupe_id);
            $query = Seance::with(['module', 'formateur'])->where('groupe_id', $groupe->id);

            // Filtrer par semestre si choisi
            if ($semestreChoisi) {
                $query->whereHas('module', fn($q) => $q->where('semestre', $semestreChoisi));
            } else {
                // Par défaut, on filtre selon le groupe impairs/pairs
                if ($groupeSemestre == 'impair') {
                    $query->whereHas('module', fn($q) => $q->whereRaw('semestre % 2 = 1'));
                } else {
                    $query->whereHas('module', fn($q) => $q->whereRaw('semestre % 2 = 0'));
                }
            }

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

        return view('directeur.emploi', compact(
            'filieres', 'groupes', 'emploi', 'groupe', 'groupeSemestre'
        ));
    }
}