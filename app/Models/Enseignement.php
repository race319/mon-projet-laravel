<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignement extends Model
{
    use HasFactory;

    protected $table = 'enseignement';
    protected $casts = [
    'date_seance' => 'date',
];


    protected $fillable = [
        'code_enseignant',
        'code_groupe',
        'code_matiere',
        'nature_enseignement',
        'date_seance',
    ];
    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant');
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'code_groupe');
    }
    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'code_matiere');
    }

    /**
     * Relation vers les absences liées à cet enseignement
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'code_matiere', 'code_matiere');
    }
}
