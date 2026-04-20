<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'mot_de_passe',
        'role', 'actif', 'bloque_time', 'tentative_echec', 'groupe_id'
    ];

    protected $hidden = ['mot_de_passe', 'remember_token'];

    protected $casts = [
        'actif' => 'boolean',
        'bloque_time' => 'datetime',
    ];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'etudiant_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'etudiant_id');
    }

    public function modulesEnseigne()
    {
        return $this->hasMany(Module::class, 'formateur_id');
    }

    public function seancesEnseigne()
    {
        return $this->hasMany(Seance::class, 'formateur_id');
    }

    public function supportCours()
    {
        return $this->hasMany(SupportCours::class, 'formateur_id');
    }
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }
    public function getTauxAbsenceParModule()
{
    $modules = Module::whereHas('seances', function($q) {
        $q->whereIn('groupe_id', $this->groupe->pluck('id')); // ou via le groupe de l'étudiant
    })->get();

    $result = [];
    foreach ($modules as $module) {
        // Total heures de séances pour ce module
        $heuresTotales = $module->seances->sum(function($seance) {
            return (strtotime($seance->h_fin) - strtotime($seance->h_debut)) / 3600;
        });
        // Heures d'absence de l'étudiant pour ce module
        $heuresAbsences = Absence::where('etudiant_id', $this->id)
            ->whereHas('seance', function($q) use ($module) {
                $q->where('module_id', $module->id);
            })->get()->sum(function($absence) {
                $seance = $absence->seance;
                return (strtotime($seance->h_fin) - strtotime($seance->h_debut)) / 3600;
            });
        $taux = $heuresTotales > 0 ? round(($heuresAbsences / $heuresTotales) * 100, 2) : 0;
        if ($taux > 20) {
            $result[] = [
                'module' => $module->nom,
                'taux' => $taux,
                'etudiant' => $this,
            ];
        }
    }
    return $result;
}
}