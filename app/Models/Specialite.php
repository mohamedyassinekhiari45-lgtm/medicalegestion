<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialite extends Model
{
    protected $fillable = ['libelle', 'description'];

    public function medecins()
    {
        return $this->belongsToMany(User::class, 'medecin_specialite', 'specialite_id', 'user_id');
    }
}
