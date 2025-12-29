<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Creneau extends Model
{
    protected $table = 'creneau';
    protected $primaryKey = 'code_creneau';
     protected $fillable = [
        'code_creneau',
        'date',
        'heure_debut',
        'heure_fin',
        
    ];

    public function voeuxExamens()
    {
        return $this->hasMany(VoeuxExamen::class, 'creneau_id');
    }
    public function scopeActif($query)
    {
        return $query->where('actif', 1);
    }
    public function scopeInactif($query)
    {
        return $query->where('actif', 0);
    }
    
}