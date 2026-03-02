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
        'code_groupe',
        'code_matiere',
        'code_enseignant',
        'seance',
        'date_absence',
        'statut',
        'justifie',
        'elimination',
        
    ];
   protected $casts = [
        'date_absence' => 'date', // ✅ Cast en objet Carbon
    ];
    
    public function inscrit()
{
    return $this->belongsTo(Inscrit::class, 'code_etudiant', 'code_etudiant');
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
