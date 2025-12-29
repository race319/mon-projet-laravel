<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $table = 'salles';
    protected $primaryKey = 'code_salle';

    protected $fillable = [
        'nom_salle'
    ];

    public function seances()
    {
        return $this->hasMany(Seance::class, 'code_salle');
    }
}
