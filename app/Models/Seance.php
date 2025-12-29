<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;
    
    protected $table = 'seances';

    protected $fillable = [
        'date_seance',
        'heure_seance',
        'numero_seance',
        'code_salle',
        'nature',
        'nb_seances',
        'code_enseignant',
        'code_groupe',
        'etat',
    ];

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'code_salle', 'code_salle');
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant', 'id'); 
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'code_groupe', 'code_groupe');
    }

    public function isAbsent()
    {
        return $this->etat == 0;
    }
}
