<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surveiller extends Model
{
    protected $table = 'surveiller';

    protected $fillable = [
        'code_enseignant',
        'code_creneau',
        'qualite',
    ];

    protected $casts = [
        'code_enseignant' => 'integer',
        'code_creneau' => 'integer',
    ];

    
    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant', 'id');
    }

   
   public function creneau()
{
    return $this->belongsTo(Creneau::class, 'code_creneau', 'code_creneau');
}

    // Accessor : Libellé de la qualité
    public function getQualiteLabelAttribute()
    {
        return $this->qualite === 'S' ? 'Surveillant' : 'Commission';
    }

    // Scope : Récupérer uniquement les surveillants (pas les commissions)
    public function scopeSurveillants($query)
    {
        return $query->where('qualite', 'S');
    }

    // Scope : Récupérer uniquement les commissions
    public function scopeCommissions($query)
    {
        return $query->where('qualite', 'C');
    }
}