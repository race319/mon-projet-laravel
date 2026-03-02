<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creneau extends Model
{
    use HasFactory;

    protected $table = 'creneau';
    protected $primaryKey = 'code_creneau'; // ✅ string primary key
    public $incrementing = false;           // ✅ pas auto increment
    protected $keyType = 'string';          // ✅ type string

    protected $fillable = [
        'code_creneau',
        'date',
        'heure_debut',
        'heure_fin',
    ];

   
    public function voeuxExamens()
    {
        return $this->hasMany(VoeuxExamen::class, 'code_creneau', 'code_creneau');
    }

 
}