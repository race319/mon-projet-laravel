<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model {
    use HasFactory;

    protected $table = 'matieres';
    protected $primaryKey = 'code_matiere';
    protected $fillable = [
        'nom_matiere',
        
    ];

    public function enseignements()
    {
        return $this->hasMany(Enseignement::class, 'code_matiere', 'code_matiere');
    }

    public function absences() {
        return $this->hasMany(Absence::class, 'code_matiere');
    }
    public function seances()
{
    return $this->hasMany(Seance::class, 'code_matiere', 'code_matiere');
}

}
