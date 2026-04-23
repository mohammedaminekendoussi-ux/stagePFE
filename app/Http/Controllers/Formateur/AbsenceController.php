<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use App\Models\Absence;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    const CRENEAUX = [
        '08:30-10:30',
        '10:30-12:30',
        '14:30-16:30',
        '16:30-18:30',
    ];

    const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];

    // Déterminer le groupe de semestre en fonction de la date
    private function getSemestreGroupeParDefaut()
    {
        $mois = now()->month;
        // Septembre (9) à Février (2) -> impairs, Mars (3) à Août (8) -> pairs
        if ($mois >= 9 || $mois <= 2) {
            return 'impair';
        } else {
            return 'pair';
        }
    }

    public function index(Request $request)
    {
        $formateur = auth()->user();
        $now = Carbon::now();
        $currentDay = $now->locale('fr')->isoFormat('dddd'); // 'Lundi'
        $currentTime = $now->format('H:i:s');
        $selectedDate = $request->get('date', $now->format('Y-m-d'));

        // Récupérer le groupe de semestre actuel (impair/pair) selon la date
        $groupeSemestre = $this->getSemestreGroupeParDefaut();

        // Toutes les séances du formateur (pour le select) filtrées par groupe de semestre
        $seances = Seance::where('formateur_id', $formateur->id)
            ->whereHas('module', function ($q) use ($groupeSemestre) {
                if ($groupeSemestre == 'impair') {
                    $q->whereRaw('semestre % 2 = 1');
                } else {
                    $q->whereRaw('semestre % 2 = 0');
                }
            })
            ->with(['module', 'groupe'])
            ->orderBy('jour', 'desc')
            ->orderBy('h_debut', 'desc')
            ->get();

        $seanceId = $request->get('seance_id');
        $seance = null;
        $etudiants = collect();
        $presences = [];

        if ($seanceId) {
            $seance = Seance::with(['module', 'groupe'])->findOrFail($seanceId);
            if ($seance->formateur_id != $formateur->id) abort(403);
            $etudiants = $seance->groupe->etudiants()->orderBy('nom')->get();
            $absences = Absence::where('seance_id', $seance->id)->pluck('etudiant_id')->toArray();
            $presences = $etudiants->pluck('id')->diff($absences)->toArray();
        } else {
            // Détection automatique de la séance en cours (dans le groupe de semestre actuel)
            $seance = Seance::where('formateur_id', $formateur->id)
                ->whereRaw('LOWER(jour) = ?', [strtolower($currentDay)])
                ->where('h_debut', '<=', $currentTime)
                ->where('h_fin', '>=', $currentTime)
                ->whereHas('module', function ($q) use ($groupeSemestre) {
                    if ($groupeSemestre == 'impair') {
                        $q->whereRaw('semestre % 2 = 1');
                    } else {
                        $q->whereRaw('semestre % 2 = 0');
                    }
                })
                ->first();
            if ($seance) {
                $etudiants = $seance->groupe->etudiants()->orderBy('nom')->get();
                $absences = Absence::where('seance_id', $seance->id)->pluck('etudiant_id')->toArray();
                $presences = $etudiants->pluck('id')->diff($absences)->toArray();
                $selectedDate = $now->format('Y-m-d');
            }
        }

        return view('formateur.absences.index', compact('seances', 'seance', 'etudiants', 'presences', 'selectedDate', 'groupeSemestre'));
    }

    public function store(Request $request, $seanceId)
    {
        $formateur = auth()->user();
        $seance = Seance::findOrFail($seanceId);
        $date = $request->input('date', now()->format('Y-m-d'));

        if ($seance->formateur_id != $formateur->id) abort(403);

        $etudiantsDuGroupe = $seance->groupe->etudiants->pluck('id')->toArray();
        $presents = $request->input('etudiants_presents', []);
        $absents = array_diff($etudiantsDuGroupe, $presents);

        // Supprimer les anciennes absences pour cette séance ET cette date
        Absence::where('seance_id', $seance->id)->where('date', $date)->delete();

        foreach ($absents as $etudiantId) {
            Absence::create([
                'date' => $date,
                'justifiee' => false,
                'seance_id' => $seance->id,
                'etudiant_id' => $etudiantId,
            ]);
        }

        return redirect()->route('formateur.absences.index', ['seance_id' => $seance->id, 'date' => $date])
                         ->with('success', 'Présences enregistrées avec succès.');
    }
}