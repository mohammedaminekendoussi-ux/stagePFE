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

    public function index()
    {
        $formateur = auth()->user();

        // Récupérer toutes les séances du formateur
        $seances = Seance::with(['module', 'groupe'])
            ->where('formateur_id', $formateur->id)
            ->get();

        // Construire le tableau emploi du temps
        $emploi = [];
        foreach (self::JOURS as $jour) {
            foreach (self::CRENEAUX as $creneau) {
                [$debut, $fin] = explode('-', $creneau);
                $seance = $seances->first(function($s) use ($jour, $debut, $fin) {
                    // Ici on suppose que la colonne 'jour' contient le nom du jour (ex: 'Lundi')
                    // Si c'est une date, utilisez Carbon::parse($s->jour)->translatedFormat('l')
                    return $s->jour === $jour
                        && $s->h_debut === $debut . ':00'
                        && $s->h_fin === $fin . ':00';
                });
                $emploi[$jour][$creneau] = $seance;
            }
        }

        return view('formateur.emploi', compact('emploi'));
    }
}