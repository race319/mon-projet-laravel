<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $table = 'groupes';
    protected $primaryKey = 'code_groupe';

    protected $fillable = [
        'nom_groupe'
    ];
    public function etudiants()
{
    return $this->belongsToMany(
        User::class,
        'inscrit',       
        'code_groupe',   
        'code_etudiant'  
    )->where('role', 'etudiant');
}


    public function seances()
    {
        return $this->hasMany(Seance::class, 'code_groupe');
    }
}
