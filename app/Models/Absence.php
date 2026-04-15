<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = ['date', 'justifiee', 'seance_id', 'etudiant_id'];

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }
}