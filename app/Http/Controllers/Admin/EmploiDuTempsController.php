<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use App\Models\Groupe;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class EmploiDuTempsController extends Controller
{
    // Créneaux horaires fixes
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

        if ($request->filled('groupe_id')) {
            $groupe  = Groupe::with('filiere')->findOrFail($request->groupe_id);
            $seances = Seance::with(['module', 'formateur'])
                ->where('groupe_id', $groupe->id)
                ->get();

            // Construire le tableau emploi du temps
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

        return view('admin.emploi.index', compact(
            'filieres', 'groupes', 'emploi', 'groupe'
        ));
    }

    // Récupérer les formateurs d'un module (pour AJAX)
    public function getFormateurs($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $formateurs = User::where('id', $module->formateur_id)
            ->where('actif', true)
            ->get(['id', 'nom', 'prenom']);

        return response()->json($formateurs);
    }

    // Vérifier les conflits
    private function verifierConflits($jour, $debut, $fin, $formateurId, $salle, $groupeId, $excludeId = null)
    {
        $query = Seance::where('jour', $jour)
            ->where(function($q) use ($debut, $fin) {
                $q->where('h_debut', $debut . ':00')
                  ->where('h_fin', $fin . ':00');
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Conflit formateur
        $conflitFormateur = (clone $query)
            ->where('formateur_id', $formateurId)
            ->exists();

        // Conflit salle
        $conflitSalle = (clone $query)
            ->where('salle', $salle)
            ->where('groupe_id', '!=', $groupeId)
            ->exists();

        return [
            'formateur' => $conflitFormateur,
            'salle'     => $conflitSalle,
        ];
    }

    // Ajouter une séance
    public function store(Request $request)
    {
        $request->validate([
            'groupe_id'    => 'required|exists:groupes,id',
            'module_id'    => 'required|exists:modules,id',
            'formateur_id' => 'required|exists:users,id',
            'jour'         => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi',
            'creneau'      => 'required',
            'salle'        => 'required|string|max:50',
        ]);

        [$debut, $fin] = explode('-', $request->creneau);

        // Vérifier conflits
        $conflits = $this->verifierConflits(
            $request->jour, $debut, $fin,
            $request->formateur_id, $request->salle,
            $request->groupe_id
        );

        if ($conflits['formateur']) {
            return back()->with('error', '⚠️ Ce formateur est déjà occupé à cet horaire !');
        }

        if ($conflits['salle']) {
            return back()->with('error', '⚠️ Cette salle est déjà réservée à cet horaire !');
        }

        Seance::create([
            'jour'         => $request->jour,
            'h_debut'      => $debut . ':00',
            'h_fin'        => $fin . ':00',
            'salle'        => $request->salle,
            'statut'       => 'prevue',
            'module_id'    => $request->module_id,
            'groupe_id'    => $request->groupe_id,
            'formateur_id' => $request->formateur_id,
        ]);

        return redirect()->route('admin.emploi.index', [
            'groupe_id'   => $request->groupe_id,
            'filiere_id'  => Groupe::find($request->groupe_id)->filiere_id,
        ])->with('success', 'Séance ajoutée avec succès !');
    }

    // Modifier une séance
    public function update(Request $request, $id)
    {
        $seance = Seance::findOrFail($id);

        $request->validate([
            'module_id'    => 'required|exists:modules,id',
            'formateur_id' => 'required|exists:users,id',
            'salle'        => 'required|string|max:50',
        ]);

        [$debut, $fin] = explode('-', $request->creneau);

        $conflits = $this->verifierConflits(
            $seance->jour, $debut, $fin,
            $request->formateur_id, $request->salle,
            $seance->groupe_id, $id
        );

        if ($conflits['formateur']) {
            return back()->with('error', '⚠️ Ce formateur est déjà occupé à cet horaire !');
        }

        if ($conflits['salle']) {
            return back()->with('error', '⚠️ Cette salle est déjà réservée à cet horaire !');
        }

        $seance->update([
            'module_id'    => $request->module_id,
            'formateur_id' => $request->formateur_id,
            'salle'        => $request->salle,
        ]);

        return redirect()->route('admin.emploi.index', [
            'groupe_id'  => $seance->groupe_id,
            'filiere_id' => $seance->groupe->filiere_id,
        ])->with('success', 'Séance modifiée avec succès !');
    }

    // Supprimer une séance
    public function destroy($id)
    {
        $seance = Seance::findOrFail($id);
        $groupeId  = $seance->groupe_id;
        $filiereId = $seance->groupe->filiere_id;
        $seance->delete();

        return redirect()->route('admin.emploi.index', [
            'groupe_id'  => $groupeId,
            'filiere_id' => $filiereId,
        ])->with('success', 'Séance supprimée avec succès !');
    }
}