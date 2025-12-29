<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Illuminate\Http\Request;

class SeanceController extends Controller
{
    
    public function filter(Request $request)
    {
        $request->validate([
            'date_seance' => 'required|date',
            'heure_seance' => 'nullable|string' 
        ]);

        $query = Seance::with(['enseignant', 'salle', 'groupe'])
            ->where('date_seance', $request->date_seance);

        if ($request->filled('heure_seance')) {
            $query->where('heure_seance', $request->heure_seance);
        }

        $seances = $query->get();

        return response()->json([
            'success' => true,
            'selected_date' => $request->date_seance, // afficher la date sélectionnée en premier
            'data' => $seances
        ], 200);
    }

   
    public function updateEtat(Request $request, $id)
    {
        $request->validate([
            'etat' => 'required|in:0,1' 
        ]);

        $seance = Seance::findOrFail($id);
        $seance->etat = $request->etat;
        $seance->save();

        return response()->json([
            'success' => true,
            'message' => 'Etat modifié avec succès',
            'data' => $seance
        ], 200);
    }
}
