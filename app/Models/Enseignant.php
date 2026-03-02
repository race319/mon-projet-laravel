<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $primaryKey = 'code_enseignant'; 
    protected $keyType = 'string';

    protected $fillable = [
        'code_enseignant',
        'charge_enseignement',
        'charge_surveillance',
        
    ];

     protected $casts = [
        'charge_enseignement' => 'float',
        'charge_surveillance' => 'float',
    ];

  
    public function user()
{
    return $this->belongsTo(User::class, 'code_enseignant', 'code_enseignant');
}

   public function voeux()
{
    return $this->hasMany(VoeuxEnseignement::class, 'code_enseignant', 'code_enseignant');
}


    public function voeuxExamen()
    {
        return $this->hasMany(VoeuxExamen::class, 'code_enseignant', 'code_enseignant');
    }
}
