<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\VoeuxEnseignement;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'code_enseignant',
    ];

    protected $hidden = [
        'password',
        
        'remember_token',
    ];

    public function groupes()
{
    return $this->belongsToMany(
        Groupe::class,
        'inscrit',       
        'code_etudiant',
        'code_groupe'    
    )->where('role', 'etudiant');
}

    public function voeux()
    {
        return $this->hasMany(VoeuxEnseignement::class, 'code_enseignant', 'code_enseignant');
    }
    
    public function enseignant()
{
    return $this->hasOne(Enseignant::class, 'user_id', 'id');
}

}
