<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VoeuxExamen extends Model
{
    protected $table = 'voeux_examens';
    protected $fillable = [
        'code_enseignant',
        'code_creneau',
    ];

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant', 'user_id');
    }

    
    public function creneau()
    {
        return $this->belongsTo(Creneau::class, 'code_creneau', 'code_creneau');
    }
}
