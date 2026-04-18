<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Models\User;
use App\Models\Absence;
use App\Models\Seance;
use App\Models\Note;
use App\Models\Module;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $filieres = Filiere::all();
        $groupes = Groupe::all();
        $formateurs = User::where('role', 'formateur')->get();

        $rapportData = null;

        if ($request->has('type_rapport')) {
            $rapportData = $this->genererRapport($request);
        }

        return view('directeur.rapports', compact('filieres', 'groupes', 'formateurs', 'rapportData'));
    }

    private function genererRapport($request)
    {
        $type = $request->type_rapport;
        $filiereId = $request->filiere_id;
        $groupeId = $request->groupe_id;
        $formateurId = $request->formateur_id;
        $dateDebut = $request->date_debut ? Carbon::parse($request->date_debut) : null;
        $dateFin = $request->date_fin ? Carbon::parse($request->date_fin) : null;

        $data = [];
        $title = '';

        switch ($type) {
            case 'absences':
                $query = Absence::with(['etudiant', 'seance.module', 'seance.groupe']);
                if ($groupeId) {
                    $query->whereHas('seance', fn($q) => $q->where('groupe_id', $groupeId));
                } elseif ($filiereId) {
                    $query->whereHas('seance.groupe', fn($q) => $q->where('filiere_id', $filiereId));
                }
                if ($formateurId) {
                    $query->whereHas('seance', fn($q) => $q->where('formateur_id', $formateurId));
                }
                if ($dateDebut) $query->whereDate('date', '>=', $dateDebut);
                if ($dateFin) $query->whereDate('date', '<=', $dateFin);
                $data = $query->get();
                $title = "Rapport d'absences";
                break;

            case 'notes':
                $query = Note::with(['etudiant', 'module']);
                if ($groupeId) {
                    $query->whereHas('etudiant', fn($q) => $q->where('groupe_id', $groupeId));
                } elseif ($filiereId) {
                    $query->whereHas('etudiant.groupe', fn($q) => $q->where('filiere_id', $filiereId));
                }
                if ($formateurId) {
                    $query->whereHas('module', fn($q) => $q->where('formateur_id', $formateurId));
                }
                $data = $query->get();
                $title = "Rapport des notes";
                break;

            case 'presence':
                $groupesQuery = Groupe::with('etudiants', 'seances');
                if ($groupeId) {
                    $groupesQuery->where('id', $groupeId);
                } elseif ($filiereId) {
                    $groupesQuery->where('filiere_id', $filiereId);
                }
                $groupesList = $groupesQuery->get();
                $resultats = [];
                foreach ($groupesList as $groupe) {
                    $nbEtudiants = $groupe->etudiants->count();
                    $seancesQuery = $groupe->seances();
                    if ($dateDebut) $seancesQuery->whereDate('jour', '>=', $dateDebut);
                    if ($dateFin) $seancesQuery->whereDate('jour', '<=', $dateFin);
                    $nbSeances = $seancesQuery->count();
                    if ($nbEtudiants > 0 && $nbSeances > 0) {
                        $absencesQuery = Absence::whereHas('seance', fn($q) => $q->where('groupe_id', $groupe->id));
                        if ($dateDebut) $absencesQuery->whereDate('date', '>=', $dateDebut);
                        if ($dateFin) $absencesQuery->whereDate('date', '<=', $dateFin);
                        $nbAbsences = $absencesQuery->count();
                        $nbPresences = ($nbEtudiants * $nbSeances) - $nbAbsences;
                        $taux = ($nbPresences / ($nbEtudiants * $nbSeances)) * 100;
                        $resultats[] = [
                            'groupe' => $groupe->nom,
                            'nb_etudiants' => $nbEtudiants,
                            'nb_seances' => $nbSeances,
                            'nb_absences' => $nbAbsences,
                            'taux_presence' => round($taux, 2),
                        ];
                    } else {
                        $resultats[] = [
                            'groupe' => $groupe->nom,
                            'nb_etudiants' => $nbEtudiants,
                            'nb_seances' => $nbSeances,
                            'nb_absences' => 0,
                            'taux_presence' => 0,
                        ];
                    }
                }
                $data = $resultats;
                $title = "Rapport de présence";
                break;

            case 'comparaison':
    $filiere1_id = $request->input('filiere_id');
    $filiere2_id = $request->input('filiere_id2');

    if (!$filiere1_id || !$filiere2_id) {
        $data = null;
        $title = "Veuillez sélectionner deux filières.";
    } else {
        $filiere1 = Filiere::find($filiere1_id);
        $filiere2 = Filiere::find($filiere2_id);

        if (!$filiere1 || !$filiere2) {
            $data = null;
            $title = "Une ou deux filières introuvables.";
        } else {
            // Effectifs
            $effectif1 = User::where('role', 'etudiant')
                ->whereHas('groupe', fn($q) => $q->where('filiere_id', $filiere1->id))
                ->count();
            $effectif2 = User::where('role', 'etudiant')
                ->whereHas('groupe', fn($q) => $q->where('filiere_id', $filiere2->id))
                ->count();

            // Taux de présence et absences
            $groupes1 = Groupe::where('filiere_id', $filiere1->id)->get();
            $groupes2 = Groupe::where('filiere_id', $filiere2->id)->get();

            $totalSeances1 = 0;
            $totalAbsences1 = 0;
            foreach ($groupes1 as $g) {
                $nbSeances = $g->seances()->count();
                $totalSeances1 += $nbSeances * $g->etudiants->count();
                $totalAbsences1 += Absence::whereHas('seance', fn($q) => $q->where('groupe_id', $g->id))->count();
            }
            $totalPresences1 = $totalSeances1 - $totalAbsences1;
            $tauxPresence1 = $totalSeances1 > 0 ? round(($totalPresences1 / $totalSeances1) * 100, 2) : 0;

            $totalSeances2 = 0;
            $totalAbsences2 = 0;
            foreach ($groupes2 as $g) {
                $nbSeances = $g->seances()->count();
                $totalSeances2 += $nbSeances * $g->etudiants->count();
                $totalAbsences2 += Absence::whereHas('seance', fn($q) => $q->where('groupe_id', $g->id))->count();
            }
            $totalPresences2 = $totalSeances2 - $totalAbsences2;
            $tauxPresence2 = $totalSeances2 > 0 ? round(($totalPresences2 / $totalSeances2) * 100, 2) : 0;

            // Moyennes des notes
            $moyenne1 = Note::whereHas('etudiant.groupe', fn($q) => $q->where('filiere_id', $filiere1->id))
                ->get()->avg(fn($n) => ($n->controle_continu + $n->examen_finale) / 2) ?? 0;
            $moyenne2 = Note::whereHas('etudiant.groupe', fn($q) => $q->where('filiere_id', $filiere2->id))
                ->get()->avg(fn($n) => ($n->controle_continu + $n->examen_finale) / 2) ?? 0;

            $data = [
                'filiere1' => $filiere1->nom,
                'filiere2' => $filiere2->nom,
                'effectif1' => $effectif1,
                'effectif2' => $effectif2,
                'taux_presence1' => $tauxPresence1,
                'taux_presence2' => $tauxPresence2,
                'total_absences1' => $totalAbsences1,
                'total_absences2' => $totalAbsences2,
                'moyenne1' => round($moyenne1, 2),
                'moyenne2' => round($moyenne2, 2),
                'difference_moyenne' => round($moyenne1 - $moyenne2, 2),
            ];
            $title = "Comparaison entre deux filières";
        }
    }
    break;

            default:
                return null;
        }

        return (object) [
            'type' => $type,
            'title' => $title,
            'data' => $data,
            'filtres' => $request->all(),
        ];
    }

    public function exportPdf(Request $request)
    {
        $rapportData = $this->genererRapport($request);
        if (!$rapportData || !$rapportData->data) {
            return back()->with('error', 'Aucune donnée à exporter.');
        }

        $pdf = Pdf::loadView('directeur.rapports_pdf', compact('rapportData'));
        return $pdf->download('rapport_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}