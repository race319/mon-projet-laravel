<?php

namespace App\Http\Controllers;

use App\Models\Inscrit;
use App\Models\Enseignement;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    public function getEtudiants($code_groupe)
    {
        $etudiants = Inscrit::where('code_groupe', $code_groupe)
            ->with('etudiant') 
            ->get();

        if ($etudiants->isEmpty()) {
            return response()->json(['message' => 'Aucun étudiant trouvé'], 404);
        }

        return response()->json($etudiants, 200);
    }

    public function getMatieres($code_groupe)
    {
        $enseignements = Enseignement::where('code_groupe', $code_groupe)
            ->with('matiere') 
            ->get();

        if ($enseignements->isEmpty()) {
            return response()->json(['message' => 'Aucune matière trouvée pour ce groupe'], 404);
        }

        $matieres = $enseignements->map(function ($e) {
            return $e->matiere;
        });

        return response()->json($matieres, 200);
    }
}