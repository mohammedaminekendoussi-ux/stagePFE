<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Filiere;
use App\Models\Groupe;
use Illuminate\Http\Request;

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
            $formateurs = collect(); // collection vide pour la vue
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
            $etudiants = collect(); // collection vide
        }

        if ($filiereId) {
            $groupes = Groupe::where('filiere_id', $filiereId)->get();
        }

        return view('directeur.dossiers', compact('type', 'etudiants', 'formateurs', 'filieres', 'groupes', 'search', 'filiereId'));
    }
}