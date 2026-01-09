<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surveiller;
use App\Models\User;
use App\Models\Creneau;

class SurveillerController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Surveiller::with(['enseignant', 'creneau']);

        if ($request->has('enseignant_id')) {
            $query->where('code_enseignant', $request->enseignant_id);
        }

        if ($request->has('creneau_id')) {
            $query->where('code_creneau', $request->creneau_id);
        }

        $surveillances = $query->get();

        return response()->json([
            'success' => true,
            'data' => $surveillances
        ]);
    }

   
    public function updateQualite(Request $request, $id)
    {
        $request->validate([
            'qualite' => 'required|in:S,C',
        ]);

        $surveillance = Surveiller::find($id);
        if (!$surveillance) {
            return response()->json([
                'success' => false,
                'message' => 'Surveillance introuvable.'
            ], 404);
        }

        $surveillance->qualite = $request->qualite;
        $surveillance->save();

        return response()->json([
            'success' => true,
            'message' => 'Qualité modifiée avec succès.',
            'data' => $surveillance
        ]);
    }
}
