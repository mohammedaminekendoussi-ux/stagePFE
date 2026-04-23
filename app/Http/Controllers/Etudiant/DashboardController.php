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

    private function getSemestreActuel($anneeGroupe)
    {
        // année du groupe : 1,2,3
        // base du semestre : (année - 1)*2 + 1  => pour année 1 -> 1, année 2 -> 3, année 3 -> 5
        $semestreBase = ($anneeGroupe - 1) * 2 + 1; // 1,3,5
        // actuellement, nous sommes soit en semestre impair (base) soit pair (base+1)
        $mois = now()->month;
        // Septembre à Février -> semestre impair (S1, S3, S5)
        if ($mois >= 9 || $mois <= 2) {
            return $semestreBase; // impair
        } else {
            return $semestreBase + 1; // pair
        }
    }

    public function index()
    {
        $etudiant = Auth::user();
        $groupe = $etudiant->groupe;

        $info = [
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'email' => $etudiant->email,
            'filiere' => $groupe ? $groupe->filiere->nom : 'Non affecté',
            'groupe' => $groupe ? $groupe->nom : 'Non affecté',
        ];

        $emploi = [];
        $semestreActuel = '';
        if ($groupe) {
            $anneeGroupe = $groupe->annee; // 1,2,3
            $semestreNumber = $this->getSemestreActuel($anneeGroupe);
            $semestreActuel = "Semestre $semestreNumber";

            $seances = Seance::with(['module', 'formateur'])
                ->where('groupe_id', $groupe->id)
                ->whereHas('module', function ($q) use ($semestreNumber) {
                    $q->where('semestre', $semestreNumber);
                })
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

        return view('etudiant.dashboard', compact('info', 'emploi', 'semestreActuel'));
    }
}