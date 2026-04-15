<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = ['jour', 'h_debut', 'h_fin', 'salle', 'statut', 'module_id', 'groupe_id', 'formateur_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
}