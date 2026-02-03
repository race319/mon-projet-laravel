<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Seance extends Model
{
    use HasFactory;
      public $timestamps = true;
    protected $table = 'seances';

    protected $fillable = [
        'date_seance',
        'code_jour',
        'numero_seance',
        'code_salle',
        'code_typeseance',
        'code_enseignant',
        'code_groupe',
        'code_matiere',
        'code_effectue',
        'code_surveillance',
        'locked_at',
    ];

    
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
