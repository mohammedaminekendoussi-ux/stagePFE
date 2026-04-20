<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['controle_continu', 'examen_finale', 'validee', 'module_id', 'etudiant_id'];
    
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }
}