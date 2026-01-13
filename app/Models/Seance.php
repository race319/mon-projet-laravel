<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Seance extends Model
{
    use HasFactory;
    
    protected $table = 'seances';

    protected $fillable = [
        'date_seance',
        'heure_seance',
        'numero_seance',
        'code_salle',
        'nature',
        'nb_seances',
        'code_enseignant',
        'code_groupe',
        'etat',
        'code_suveillance',
        'locked_at',
    ];

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'code_salle', 'code_salle');
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant', 'id'); 
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'code_groupe', 'code_groupe');
    }

    public function isAbsent()
    {
        return $this->etat == 0;
    }

    public function surveillant() 
    { 
        return $this->belongsTo(User::class, 'code_suveillance', 'id'); 
    }
    public function isLocked()
    {
        if (!$this->locked_at) return false;

        $limit = Carbon::parse($this->locked_at)
            ->addSeconds(config('seances.absence_modification_seconds'));

        return now()->greaterThan($limit);
    }
}
