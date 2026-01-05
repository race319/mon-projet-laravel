<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enseignement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EnseignantController extends Controller
{

 public function getGroupes(Request $request, $code_enseignant)
{
   
    \Log::info("========== DEBUT getGroupes ==========");
    \Log::info("📅 Date reçue : " . ($request->query('date') ?? 'NULL'));
    \Log::info("👤 Code enseignant : " . $code_enseignant);
    \Log::info("🌐 URL complète : " . $request->fullUrl());
    \Log::info("📦 Tous les paramètres : " . json_encode($request->all()));

    $date = $request->query('date');

    if (!$date) {
        \Log::warning("⚠️ Aucune date fournie - Retour tableau vide");
        return response()->json([], 200);
    }

    
    \Log::info("🔍 Recherche avec :");
    \Log::info("  - code_enseignant = " . $code_enseignant);
    \Log::info("  - date_seance = " . $date);

    $groupes = Enseignement::where('code_enseignant', $code_enseignant)
        ->whereDate('date_seance', $date)
        ->with('groupe')
        ->get();

   
    \Log::info("📦 Nombre de groupes trouvés : " . $groupes->count());
    
    if ($groupes->count() > 0) {
        \Log::info("✅ Groupes : " . $groupes->pluck('id')->toJson());
        \Log::info("📝 Détails premier groupe : " . $groupes->first()->toJson());
    } else {
        \Log::warning("⚠️ AUCUN groupe trouvé !");
        
       
        $totalEnseignements = Enseignement::where('code_enseignant', $code_enseignant)->count();
        \Log::info("📊 Total enseignements pour cet enseignant : " . $totalEnseignements);
        
       
        $dates = Enseignement::where('code_enseignant', $code_enseignant)
            ->pluck('date_seance')
            ->unique()
            ->toArray();
        \Log::info("📅 Dates disponibles : " . json_encode($dates));
    }

    \Log::info("========== FIN getGroupes ==========\n");

    return response()->json($groupes, 200);
}
public function getCharge(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'enseignant') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $enseignant = $user->enseignant;

        if (!$enseignant) {
            return response()->json(['message' => 'Enseignant non trouvé'], 404);
        }

        return response()->json([
            'charge_enseignement' => $enseignant->charge_enseignement
        ], 200);
    }

}
