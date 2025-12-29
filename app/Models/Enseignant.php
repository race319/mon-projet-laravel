<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'charge_enseignement',
        'charge_surveillance',
        
    ];

  
    public function user()
    {
        return $this->belongsTo(User::class);
    }
   public function voeux()
{
    return $this->hasMany(VoeuxEnseignement::class, 'code_enseignant', 'user.code_enseignant');
}


    public function voeuxExamen()
    {
        return $this->hasMany(VoeuxExamen::class, 'code_enseignant', 'user_id');
    }
}
