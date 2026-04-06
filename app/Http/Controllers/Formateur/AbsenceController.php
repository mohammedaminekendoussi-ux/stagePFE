<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use App\Models\Absence;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $formateur = auth()->user();
        $now = Carbon::now();
        $currentDay = $now->locale('fr')->isoFormat('dddd');
        $currentTime = $now->format('H:i:s');

        // Toutes les séances du formateur (pour la liste déroulante)
        $seances = Seance::where('formateur_id', $formateur->id)
            ->with(['module', 'groupe'])
            ->orderBy('jour', 'desc')
            ->orderBy('h_debut', 'desc')
            ->get();

        $seanceId = $request->get('seance_id');
        $selectedDate = $request->get('date', $now->format('Y-m-d'));

        $seance = null;
        $etudiants = collect();
        $presences = [];

        if ($seanceId) {
            // Sélection manuelle d'une séance
            $seance = Seance::with(['module', 'groupe'])->findOrFail($seanceId);
            if ($seance->formateur_id != $formateur->id) abort(403);
            $etudiants = $seance->groupe->etudiants()->orderBy('nom')->get();
            $absences = Absence::where('seance_id', $seance->id)->pluck('etudiant_id')->toArray();
            $presences = $etudiants->pluck('id')->diff($absences)->toArray();
        } else {
            // Détection automatique de la séance en cours
            $seance = Seance::where('formateur_id', $formateur->id)
                ->whereRaw('LOWER(jour) = ?', [strtolower($currentDay)])
                ->where('h_debut', '<=', $currentTime)
                ->where('h_fin', '>=', $currentTime)
                ->first();
            if ($seance) {
                $etudiants = $seance->groupe->etudiants()->orderBy('nom')->get();
                $absences = Absence::where('seance_id', $seance->id)->pluck('etudiant_id')->toArray();
                $presences = $etudiants->pluck('id')->diff($absences)->toArray();
                $selectedDate = $now->format('Y-m-d');
            }
        }

        return view('formateur.absences.index', compact('seances', 'seance', 'etudiants', 'presences', 'selectedDate'));
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

        // Supprimer les anciennes absences pour cette séance (remplacement)
        Absence::where('seance_id', $seance->id)->delete();

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