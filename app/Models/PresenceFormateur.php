<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresenceFormateur extends Model
{
    protected $table = 'presence_formateurs';

    protected $fillable = ['seance_id', 'date'];

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
}