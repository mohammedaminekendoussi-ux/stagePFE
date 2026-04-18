@extends('layouts.simple')

@section('title', 'Tableau de bord Étudiant')

@section('content')
    <div class="card">
        <div class="card-header bg-info text-white">
            <h4>Bienvenue, Étudiant</h4>
        </div>
        <div class="card-body">
            <p>Vous pourrez ici :</p>
            <ul>
                <li>Consulter votre emploi du temps</li>
                <li>Voir vos notes</li>
                <li>Suivre vos absences</li>
                <li>Télécharger les supports de cours</li>
            </ul>
        </div>
    </div>
@endsection