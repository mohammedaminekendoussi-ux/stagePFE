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
    const CRENEAUX = [
        '08:30-10:30',
        '10:30-12:30',
        '14:30-16:30',
        '16:30-18:30',
    ];

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    const SALLES = [
        'Salle A1', 'Salle A2', 'Salle A3',
        'Salle B1', 'Salle B2', 'Salle B3',
        'Salle C1', 'Salle C2', 'Labo Info 1',
        'Labo Info 2', 'Amphi 1', 'Amphi 2',
    ];

    private function getSemestresPossibles($annee)
    {
        switch ($annee) {
            case 1: return [1, 2];
            case 2: return [3, 4];
            case 3: return [5, 6];
            default: return [1, 2];
        }
    }

    private function getSemestreParAnneeEtDate($annee)
    {
        $semestresPossibles = $this->getSemestresPossibles($annee);
        $mois = now()->month;
        $isImpair = ($mois >= 9 || $mois <= 2);
        
        if ($isImpair) {
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 1) return $s;
            }
        } else {
            foreach ($semestresPossibles as $s) {
                if ($s % 2 == 0) return $s;
            }
        }
        return $semestresPossibles[0] ?? 1;
    }

    public function index(Request $request)
    {
        $filieres = Filiere::all();
        $groupes = collect();
        $emploi = [];
        $groupe = null;
        $semestre = null;

        if ($request->filled('groupe_id')) {
            $groupe = Groupe::with('filiere')->findOrFail($request->groupe_id);
            $semestre = $this->getSemestreParAnneeEtDate($groupe->annee);
            
            $seances = Seance::with(['module', 'formateur'])
                ->where('groupe_id', $groupe->id)
                ->whereHas('module', fn($q) => $q->where('semestre', $semestre))
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

        if ($request->filled('filiere_id')) {
            $groupes = Groupe::where('filiere_id', $request->filiere_id)->get();
        }

        return view('admin.emploi.index', compact('filieres', 'groupes', 'emploi', 'groupe', 'semestre'));
    }

    public function getFormateurs(Request $request, $moduleId = null)
    {
        $jour    = $request->jour;
        $creneau = $request->creneau;
        $excludeId = $request->exclude_id;
        $groupeId = $request->groupe_id;
        $semestre = $request->semestre;
        
        if (!$moduleId || !$groupeId || !$semestre) {
            return response()->json([]);
        }

        $module = Module::find($moduleId);
        if (!$module) return response()->json([]);

        $formateur = User::where('id', $module->formateur_id)
            ->where('actif', true)
            ->first(['id', 'nom', 'prenom']);

        if (!$formateur) return response()->json([]);

        $disponible = true;
        if ($jour && $creneau) {
            [$debut, $fin] = explode('-', $creneau);
            $existe = Seance::where('jour', $jour)
                ->where('h_debut', $debut . ':00')
                ->where('h_fin', $fin . ':00')
                ->where('formateur_id', $formateur->id)
                ->whereHas('module', fn($q) => $q->whereRaw('semestre % 2 = ?', [$semestre % 2]))
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();
            $disponible = !$existe;
        }

        return response()->json([[
            'id'         => $formateur->id,
            'nom'        => $formateur->nom,
            'prenom'     => $formateur->prenom,
            'disponible' => $disponible,
        ]]);
    }

    public function getSallesDisponibles(Request $request)
    {
        $jour    = $request->jour;
        $creneau = $request->creneau;
        $excludeId = $request->exclude_id;
        $groupeId = $request->groupe_id;
        $semestre = $request->semestre;

        if (!$groupeId || !$semestre) return response()->json([]);

        [$debut, $fin] = explode('-', $creneau);

        $query = Seance::where('jour', $jour)
            ->where('h_debut', $debut . ':00')
            ->where('h_fin', $fin . ':00')
            ->whereHas('module', fn($q) => $q->whereRaw('semestre % 2 = ?', [$semestre % 2]))
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));

        $sallesOccupees = $query->pluck('salle')->toArray();

        $salles = collect(self::SALLES)->map(function($salle) use ($sallesOccupees) {
            return [
                'nom'        => $salle,
                'disponible' => !in_array($salle, $sallesOccupees),
            ];
        });

        return response()->json($salles);
    }

    private function verifierConflits($jour, $debut, $fin, $formateurId, $salle, $groupeId, $excludeId = null, $semestre = null)
    {
        $query = Seance::where('jour', $jour)
            ->where(function($q) use ($debut, $fin) {
                $q->where('h_debut', $debut . ':00')
                  ->where('h_fin', $fin . ':00');
            });

        if ($excludeId) $query->where('id', '!=', $excludeId);
        if ($semestre !== null) {
            $query->whereHas('module', fn($q) => $q->whereRaw('semestre % 2 = ?', [$semestre % 2]));
        }

        $conflitFormateur = (clone $query)->where('formateur_id', $formateurId)->exists();
        $conflitSalle = (clone $query)->where('salle', $salle)->where('groupe_id', '!=', $groupeId)->exists();

        return ['formateur' => $conflitFormateur, 'salle' => $conflitSalle];
    }

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

        $groupe = Groupe::findOrFail($request->groupe_id);
        $semestre = $this->getSemestreParAnneeEtDate($groupe->annee);

        [$debut, $fin] = explode('-', $request->creneau);

        $conflits = $this->verifierConflits(
            $request->jour, $debut, $fin,
            $request->formateur_id, $request->salle,
            $request->groupe_id, null, $semestre
        );

        if ($conflits['formateur']) return back()->with('error', '⚠️ Ce formateur est déjà occupé à cet horaire pour ce semestre (impairs entre eux, pairs entre eux) !');
        if ($conflits['salle']) return back()->with('error', '⚠️ Cette salle est déjà réservée à cet horaire pour ce semestre (impairs entre eux, pairs entre eux) !');

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
            'filiere_id'  => $groupe->filiere_id,
        ])->with('success', 'Séance ajoutée avec succès !');
    }

    public function update(Request $request, $id)
    {
        $seance = Seance::findOrFail($id);

        $request->validate([
            'module_id'    => 'required|exists:modules,id',
            'formateur_id' => 'required|exists:users,id',
            'salle'        => 'required|string|max:50',
        ]);

        $groupe = Groupe::findOrFail($seance->groupe_id);
        $semestre = $this->getSemestreParAnneeEtDate($groupe->annee);

        [$debut, $fin] = explode('-', $request->creneau);

        $conflits = $this->verifierConflits(
            $seance->jour, $debut, $fin,
            $request->formateur_id, $request->salle,
            $seance->groupe_id, $id, $semestre
        );

        if ($conflits['formateur']) return back()->with('error', '⚠️ Ce formateur est déjà occupé à cet horaire pour ce semestre (impairs entre eux, pairs entre eux) !');
        if ($conflits['salle']) return back()->with('error', '⚠️ Cette salle est déjà réservée à cet horaire pour ce semestre (impairs entre eux, pairs entre eux) !');

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