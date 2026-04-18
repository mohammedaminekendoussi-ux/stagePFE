@extends('layouts.simple')

@section('title', 'Tableau de bord Formateur')

@section('content')
    <div class="card">
        <div class="card-header bg-success text-white">
            <h4>Bienvenue, Formateur</h4>
        </div>
        <div class="card-body">
            <p>Vous pourrez ici :</p>
            <ul>
                <li>Consulter votre emploi du temps</li>
                <li>Déposer des supports de cours</li>
                <li>Saisir les notes</li>
                <li>Gérer les absences</li>
            </ul>
        </div>
    </div>
@endsection