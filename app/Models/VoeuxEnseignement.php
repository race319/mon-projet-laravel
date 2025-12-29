<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VoeuxEnseignement extends Model
{
    use HasFactory;
    protected $table = 'voeux_enseignement';

    protected $fillable = ['code_enseignant', 'code_jour', 'code_seance'];

  public function enseignant()
{
    return $this->belongsTo(Enseignant::class, 'code_enseignant', 'user_id');
    //                                          ↑ voeux table      ↑ enseignants table
}
public function horaire()
    {
        return $this->belongsTo(Horaire::class, 'code_seance', 'id');
    }




    
}
