<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        // Informations personnelles
        $info = [
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'email' => $etudiant->email,
            'filiere' => $groupe ? $groupe->filiere->nom : 'Non affecté',
            'groupe' => $groupe ? $groupe->nom : 'Non affecté',
        ];

        // Emploi du temps du groupe
        $emploi = [];
        if ($groupe) {
            $seances = Seance::with(['module', 'formateur'])
                ->where('groupe_id', $groupe->id)
                ->get();

            foreach (self::JOURS as $jour) {
                foreach (self::CRENEAUX as $creneau) {
                    [$debut, $fin] = explode('-', $creneau);
                    $seance = $seances->first(function ($s) use ($jour, $debut, $fin) {
                        return $s->jour === $jour
                            && $s->h_debut === $debut . ':00'
                            && $s->h_fin === $fin . ':00';
                    });
                    $emploi[$jour][$creneau] = $seance;
                }
            }
        }

        return view('etudiant.dashboard', compact('info', 'emploi'));
    }
}