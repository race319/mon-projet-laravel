<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $table = 'absence';

    protected $fillable = [
        'code_etudiant',
        'code_matiere',
        'code_enseignant',
        'date_absence',
        'seance',
        'statut',
        'justifie',
    ];
    protected $appends = ['date_absence'];
    
    public function getDateAbsenceAttribute()
    {
        return $this->created_at;
    }

    
    public function etudiant()
    {
        return $this->belongsTo(User::class, 'code_etudiant');
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'code_enseignant');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'code_matiere');
    }
}
