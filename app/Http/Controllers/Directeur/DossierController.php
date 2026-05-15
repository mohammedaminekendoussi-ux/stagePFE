<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\Seance;
use App\Models\PresenceFormateur;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DossierController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'etudiants');
        $search = $request->get('search');
        $filiereId = $request->get('filiere_id');

        $filieres = Filiere::all();
        $groupes = collect();

        // Récupération des étudiants
        if ($type == 'etudiants') {
            $query = User::where('role', 'etudiant')->with('groupe.filiere');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%$search%")
                      ->orWhere('prenom', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }
            if ($filiereId) {
                $query->whereHas('groupe', function($q) use ($filiereId) {
                    $q->where('filiere_id', $filiereId);
                });
            }
            $etudiants = $query->orderBy('nom')->get();
            $formateurs = collect();
        } 
        // Récupération des formateurs
        else {
            $query = User::where('role', 'formateur');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%$search%")
                      ->orWhere('prenom', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            }
            $formateurs = $query->orderBy('nom')->get();
            $etudiants = collect();
        }

        if ($filiereId) {
            $groupes = Groupe::where('filiere_id', $filiereId)->get();
        }

        return view('directeur.dossiers', compact('type', 'etudiants', 'formateurs', 'filieres', 'groupes', 'search', 'filiereId'));
    }

    public function showFormateurAbsences($id)
    {
        $formateur = User::findOrFail($id);
        // Récupérer toutes les séances du formateur
        $seances = Seance::with(['module', 'groupe'])
            ->where('formateur_id', $formateur->id)
            ->orderBy('jour', 'desc')
            ->orderBy('h_debut', 'desc')
            ->get();

        // Pour chaque séance, récupérer les dates où le formateur a été présent
        $seancesAvecDates = [];
        foreach ($seances as $seance) {
            $dates = PresenceFormateur::where('seance_id', $seance->id)
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('d/m/Y'));
            $seancesAvecDates[] = [
                'seance' => $seance,
                'dates' => $dates,
            ];
        }

        return view('directeur.formateur_absences', compact('formateur', 'seancesAvecDates'));
    }
}