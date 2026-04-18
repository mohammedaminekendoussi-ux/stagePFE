<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\User;
use App\Models\Absence;
use App\Models\Seance;
use App\Models\Module;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filiereIdPresence = $request->input('filiere_id_presence');
        $filiereIdMoyenne  = $request->input('filiere_id_moyenne');

        $totalEtudiants = User::where('role', 'etudiant')->count();
        $totalModules = Module::count();
        $totalGroupes = Groupe::count();

        // Effectifs par filière
        $effectifsParFiliere = Filiere::leftJoin('groupes', 'filieres.id', '=', 'groupes.filiere_id')
            ->leftJoin('users', 'groupes.id', '=', 'users.groupe_id')
            ->where('users.role', 'etudiant')
            ->select('filieres.id', 'filieres.nom', DB::raw('count(users.id) as etudiants_count'))
            ->groupBy('filieres.id', 'filieres.nom')
            ->get();
        $allFilieres = Filiere::all();
        $effectifsParFiliere = $allFilieres->map(function($filiere) use ($effectifsParFiliere) {
            $found = $effectifsParFiliere->firstWhere('id', $filiere->id);
            return (object) [
                'nom' => $filiere->nom,
                'etudiants_count' => $found ? $found->etudiants_count : 0
            ];
        });

        // Taux de présence par groupe (filtre)
        $groupesPresence = Groupe::with('etudiants', 'seances');
        if ($filiereIdPresence) {
            $groupesPresence->where('filiere_id', $filiereIdPresence);
        }
        $groupesPresence = $groupesPresence->get();

        $tauxPresenceParGroupe = [];
        $totalPresence = 0;
        $totalSeances = 0;
        foreach ($groupesPresence as $groupe) {
            $nbEtudiants = $groupe->etudiants->count();
            $nbSeances = $groupe->seances->count();
            if ($nbEtudiants > 0 && $nbSeances > 0) {
                $nbAbsences = Absence::whereHas('seance', function($q) use ($groupe) {
                    $q->where('groupe_id', $groupe->id);
                })->count();
                $nbPresences = ($nbEtudiants * $nbSeances) - $nbAbsences;
                $taux = ($nbPresences / ($nbEtudiants * $nbSeances)) * 100;
                $tauxPresenceParGroupe[$groupe->nom] = round($taux, 2);
                $totalPresence += $nbPresences;
                $totalSeances += ($nbEtudiants * $nbSeances);
            } else {
                $tauxPresenceParGroupe[$groupe->nom] = 0;
            }
        }
        $tauxPresenceGlobal = $totalSeances > 0 ? round(($totalPresence / $totalSeances) * 100, 2) : 0;

        // Modules les plus absents
        $modulesImpact = Module::leftJoin('seances', 'modules.id', '=', 'seances.module_id')
            ->leftJoin('absences', 'seances.id', '=', 'absences.seance_id')
            ->select('modules.id', 'modules.nom', DB::raw('count(absences.id) as total_absences'))
            ->groupBy('modules.id', 'modules.nom')
            ->orderBy('total_absences', 'desc')
            ->take(5)
            ->get();

        // Moyennes par groupe (filtre)
        $groupesMoyenne = Groupe::with('etudiants');
        if ($filiereIdMoyenne) {
            $groupesMoyenne->where('filiere_id', $filiereIdMoyenne);
        }
        $groupesMoyenne = $groupesMoyenne->get();

        $moyennesParGroupe = [];
        foreach ($groupesMoyenne as $groupe) {
            $etudiantsIds = $groupe->etudiants->pluck('id');
            if ($etudiantsIds->isEmpty()) {
                $moyennesParGroupe[$groupe->nom] = 0;
                continue;
            }
            $notes = Note::whereIn('etudiant_id', $etudiantsIds)->get();
            if ($notes->count() > 0) {
                $sommeNotes = 0;
                foreach ($notes as $note) {
                    $moyenneEtudiant = ($note->controle_continu + $note->examen_finale) / 2;
                    $sommeNotes += $moyenneEtudiant;
                }
                $moyennesParGroupe[$groupe->nom] = round($sommeNotes / $notes->count(), 2);
            } else {
                $moyennesParGroupe[$groupe->nom] = 0;
            }
        }

        $filieres = Filiere::all();

        return view('directeur.dashboard', compact(
            'totalEtudiants',
            'totalModules',
            'totalGroupes',
            'effectifsParFiliere',
            'tauxPresenceParGroupe',
            'tauxPresenceGlobal',
            'modulesImpact',
            'moyennesParGroupe',
            'filieres'
        ));
    }
}