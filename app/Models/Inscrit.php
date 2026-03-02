<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscrit extends Model
{
    use HasFactory;

    protected $table = 'inscrit'; 
   

    protected $fillable = [
        'code_etudiant',
        'code_groupe'
    ];


    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'code_groupe', 'code_groupe');
    }
}
