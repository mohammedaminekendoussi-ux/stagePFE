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
}