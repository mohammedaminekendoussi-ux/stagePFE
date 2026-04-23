<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $rapportData->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .filters { font-size: 12px; margin-bottom: 15px; }
        .comparison-box { margin-top: 30px; text-align: center; }
        .filiere-card { display: inline-block; width: 45%; margin: 10px; padding: 15px; border: 1px solid #ccc; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $rapportData->title }}</h2>
        <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="filters">
        <strong>Filtres :</strong>
        @if($rapportData->filtres['filiere_id'] ?? false) Filière: {{ \App\Models\Filiere::find($rapportData->filtres['filiere_id'])->nom ?? '' }} | @endif
        @if($rapportData->filtres['filiere_id2'] ?? false) Deuxième filière: {{ \App\Models\Filiere::find($rapportData->filtres['filiere_id2'])->nom ?? '' }} | @endif
        @if($rapportData->filtres['groupe_id'] ?? false) Groupe: {{ \App\Models\Groupe::find($rapportData->filtres['groupe_id'])->nom ?? '' }} | @endif
        @if($rapportData->filtres['formateur_id'] ?? false) Formateur: {{ \App\Models\User::find($rapportData->filtres['formateur_id'])->prenom ?? '' }} {{ \App\Models\User::find($rapportData->filtres['formateur_id'])->nom ?? '' }} | @endif
        @if($rapportData->filtres['semestre'] ?? false) Semestre: {{ $rapportData->filtres['semestre'] }} | @endif
    </div>

    @if($rapportData->type == 'comparaison')
        @if(is_array($rapportData->data) && isset($rapportData->data['filiere1']))
            <h3>Comparaison entre {{ $rapportData->data['filiere1'] }} et {{ $rapportData->data['filiere2'] }}</h3>
            <table>
                <thead>
                    <tr><th>Indicateur</th><th>{{ $rapportData->data['filiere1'] }}</th><th>{{ $rapportData->data['filiere2'] }}</th></tr>
                </thead>
                <tbody>
                    <tr><td><strong>Effectif</strong></td><td>{{ $rapportData->data['effectif1'] }}</td><td>{{ $rapportData->data['effectif2'] }}</td></tr>
                    <tr><td><strong>Taux de présence</strong></td><td>{{ $rapportData->data['taux_presence1'] }}%</td><td>{{ $rapportData->data['taux_presence2'] }}%</td></tr>
                    <tr><td><strong>Total absences</strong></td><td>{{ $rapportData->data['total_absences1'] }}</td><td>{{ $rapportData->data['total_absences2'] }}</td></tr>
                    <tr><td><strong>Moyenne générale</strong></td><td>{{ $rapportData->data['moyenne1'] }}/20</td><td>{{ $rapportData->data['moyenne2'] }}/20</td></tr>
                    <tr><td><strong>Différence moyenne</strong></td><td colspan="2">{{ $rapportData->data['difference_moyenne'] }} points</td></tr>
                </tbody>
            </table>
        @else
            <p>{{ $rapportData->title ?? 'Données non disponibles' }}</p>
        @endif
    @else
        <table>
            <thead>
                <tr>
                    @if($rapportData->type == 'absences')
                        <th>Étudiant</th><th>Module</th><th>Groupe</th><th>Date</th><th>Justifiée</th>
                    @elseif($rapportData->type == 'notes')
                        <th>Étudiant</th><th>Module</th><th>Contrôle continu</th><th>Examen final</th><th>Moyenne</th>
                    @elseif($rapportData->type == 'presence')
                        <th>Groupe</th><th>Nb étudiants</th><th>Séances théoriques</th><th>Absences réelles</th><th>Taux présence (%)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rapportData->data as $item)
                <tr>
                    @if($rapportData->type == 'absences')
                        <td>{{ $item->etudiant->prenom }} {{ $item->etudiant->nom }}</td>
                        <td>{{ $item->seance->module->nom ?? '?' }}</td>
                        <td>{{ $item->seance->groupe->nom ?? '?' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                        <td>{{ $item->justifiee ? 'Oui' : 'Non' }}</td>
                    @elseif($rapportData->type == 'notes')
                        <td>{{ $item->etudiant->prenom }} {{ $item->etudiant->nom }}</td>
                        <td>{{ $item->module->nom }}</td>
                        <td>{{ $item->controle_continu ?? '-' }}</td>
                        <td>{{ $item->examen_finale ?? '-' }}</td>
                        <td>{{ round(($item->controle_continu + $item->examen_finale)/2, 2) }}</td>
                    @elseif($rapportData->type == 'presence')
                        <td>{{ is_array($item) ? $item['groupe'] : $item->groupe }}</td>
                        <td>{{ is_array($item) ? $item['nb_etudiants'] : $item->nb_etudiants }}</td>
                        <td>{{ is_array($item) ? $item['seances_theoriques'] : $item->seances_theoriques }}</td>
                        <td>{{ is_array($item) ? $item['absences_reelles'] : $item->absences_reelles }}</td>
                        <td>{{ is_array($item) ? $item['taux_presence'] : $item->taux_presence }}%</td>
                    @endif
                </tr>
                @empty
                    <tr><td colspan="5">Aucune donnée trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>