<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['nom', 'coefficient', 'volume_horaire', 'filiere_id', 'formateur_id'];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function supportCours()
    {
        return $this->hasMany(SupportCours::class);
    }
    public function absences()
{
    return $this->hasManyThrough(Absence::class, Seance::class, 'module_id', 'seance_id');
}
}