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
        'elimination',
        
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
    public function isElimine()
    {
        return $this->elimination == 1;
    }

    public function scopeElimine($query)
    {
        return $query->where('elimination', 1);
    }

    public function scopeNonElimine($query)
    {
        return $query->where('elimination', 0);
    }

    public static function nombreAbsences($codeEtudiant, $codeMatiere)
    {
        return self::where('code_etudiant', $codeEtudiant)
                   ->where('code_matiere', $codeMatiere)
                   ->where('statut', 'Absent')
                   ->count();
    }
    public static function estElimine($codeEtudiant, $codeMatiere)
    {
        return self::where('code_etudiant', $codeEtudiant)
                   ->where('code_matiere', $codeMatiere)
                   ->where('elimination', 1)
                   ->exists();
    }
    public static function changerElimination($codeEtudiant, $codeMatiere, $valeur)
{
    return self::where('code_etudiant', $codeEtudiant)
               ->where('code_matiere', $codeMatiere)
               ->update(['elimination' => $valeur]);
}

    

}
