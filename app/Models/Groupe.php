<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $table = 'groupes';
    protected $primaryKey = 'code_groupe';

    public $incrementing = false;      // ✅ MANQUANT - clé non auto-increment
    protected $keyType = 'string'; 

    protected $fillable = [
        'nom_groupe'
    ];
   


    public function seances()
    {
        return $this->hasMany(Seance::class, 'code_groupe');
    }
}
