<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['nom', 'annee', 'filiere_id'];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function etudiants()
    {
        return $this->hasMany(User::class, 'groupe_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }
}