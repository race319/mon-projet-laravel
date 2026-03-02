<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
    use HasFactory;
    protected $table = 'horaires';

    protected $fillable = [
        'jour',
        'creneau'
    ];

    public function voeux()
    {
        return $this->hasMany(VoeuxEnseignement::class, 'code_seance', 'id');
    }
}