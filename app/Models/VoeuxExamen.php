<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoeuxExamen extends Model
{
    use HasFactory;

    protected $table = 'voeux_examens';

    protected $fillable = [
        'code_enseignant',
        'code_creneau',
    ];

    
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'code_enseignant', 'code_enseignant');
    }

    
    public function creneau()
    {
        return $this->belongsTo(Creneau::class, 'code_creneau', 'code_creneau');
    }
}