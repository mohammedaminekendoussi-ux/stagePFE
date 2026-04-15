<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCours extends Model
{
    protected $fillable = ['titre', 'taille', 'date_upload', 'module_id', 'formateur_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }
}