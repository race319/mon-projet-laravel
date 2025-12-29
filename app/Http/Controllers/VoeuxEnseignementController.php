<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoeuxEnseignement;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class VoeuxEnseignementController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();
        $enseignant = $user->enseignant;

        if (!$enseignant) {
            return response()->json(['message' => 'Profil enseignant introuvable.'], 404);
        }

        $voeux = VoeuxEnseignement::where('code_enseignant', $user->id)
            ->with(['horaire']) 
            ->get();

        $heuresParSeance = 2;
        $heuresSelectionnees = $voeux->count() * $heuresParSeance;
        $reste = $enseignant->charge_enseignement - $heuresSelectionnees;

        return response()->json([
            'voeux' => $voeux,
            'charge_totale' => $enseignant->charge_enseignement,
            'heures_selectionnees' => $heuresSelectionnees,
            'reste' => max(0, $reste)
        ], 200);
    }
    public function store(Request $request)
{
    $request->validate([
        'code_jour' => 'required|integer',
        'code_seance' => 'required|integer',
    ]);

    $user = auth()->user();
    $enseignant = $user->enseignant;

    if (!$enseignant) {
        return response()->json([
            'message' => 'Profil enseignant introuvable.'
        ], 404);
    }

    $heuresParSeance = 2;
    $chargeTotale = $enseignant->charge_enseignement;

    $heuresSelectionnees = VoeuxEnseignement::where('code_enseignant', $user->id)
        ->count() * $heuresParSeance;

    \Log::info('Debug calcul heures:', [
        'user_id' => $user->id,
        'charge_totale' => $chargeTotale,
        'heures_deja_selectionnees' => $heuresSelectionnees,
        'heures_par_seance' => $heuresParSeance,
        'total_apres_ajout' => $heuresSelectionnees + $heuresParSeance,
        'nombre_voeux_existants' => VoeuxEnseignement::where('code_enseignant', $user->id)->count(),
    ]);

    
    if (($heuresSelectionnees + $heuresParSeance) > $chargeTotale) {
        $reste = $chargeTotale - $heuresSelectionnees;
        
        return response()->json([
            'message' => "Vous ne pouvez plus sélectionner cette séance. Il vous reste $reste heure(s) à compléter.",
            'debug' => [
                'charge_totale' => $chargeTotale,
                'heures_selectionnees' => $heuresSelectionnees,
                'heures_a_ajouter' => $heuresParSeance,
                'reste' => $reste
            ]
        ], 400);
    }

    
    $voeuExistant = VoeuxEnseignement::where('code_enseignant', $user->id)
        ->where('code_jour', $request->code_jour)
        ->where('code_seance', $request->code_seance)
        ->first();

    if ($voeuExistant) {
        return response()->json([
            'message' => 'Vous avez déjà sélectionné cette séance.'
        ], 400);
    }

    
    $voeu = VoeuxEnseignement::create([
        'code_enseignant' => $user->id,
        'code_jour' => $request->code_jour,
        'code_seance' => $request->code_seance,
        
    ]);

  
    $heuresSelectionnees += $heuresParSeance;
    $reste = $chargeTotale - $heuresSelectionnees;

    return response()->json([
        'message' => $reste > 0
            ? "Vœu ajouté avec succès. Il vous reste $reste heure(s) à sélectionner."
            : "Vœu ajouté avec succès. Vous avez atteint votre charge d'enseignement.",
        'reste' => $reste,
        'data' => $voeu
    ], 201);
}

public function bulkUpdate(Request $request)
    {
        $request->validate([
            'voeux' => 'required|array',
            'voeux.*.code_jour' => 'required|integer|min:1|max:7',
            'voeux.*.code_seance' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $enseignant = $user->enseignant;

        if (!$enseignant) {
            return response()->json(['message' => 'Profil enseignant introuvable.'], 404);
        }

        $heuresParSeance = 2;
        $chargeTotale = $enseignant->charge_enseignement;
        $nouveauxVoeux = $request->voeux;

        $heuresNouveaux = count($nouveauxVoeux) * $heuresParSeance;

        if ($heuresNouveaux > $chargeTotale) {
            return response()->json([
                'message' => "Le nombre de séances sélectionnées dépasse votre charge d'enseignement ($chargeTotale heures)."
            ], 400);
        }

        
        VoeuxEnseignement::where('code_enseignant', $user->id)->delete();

        $voeuxCrees = [];
        foreach ($nouveauxVoeux as $voeu) {
            $voeuxCrees[] = VoeuxEnseignement::create([
                'code_enseignant' => $user->id,
                'code_jour' => $voeu['code_jour'],
                'code_seance' => $voeu['code_seance'],
            ]);
        }

        $reste = max(0, $chargeTotale - $heuresNouveaux);

        return response()->json([
            'message' => 'Vœux mis à jour avec succès.',
            'reste' => $reste,
            'data' => $voeuxCrees
        ], 200);
    }
    public function indexx()
    {
        $creneaux = Creneau::all(); 
        return response()->json([
            'success' => true,
            'data' => $creneaux
        ], 200);
    }
    public function destroy(Request $request, $id)
{
    $user = auth()->user();
    $enseignant = $user->enseignant;

    if (!$enseignant) {
        return response()->json([
            'message' => 'Profil enseignant introuvable.'
        ], 404);
    }

    $voeu = VoeuxEnseignement::where('code_enseignant', $user->id)
        ->where('id', $id)
        ->first();

    if (!$voeu) {
        return response()->json([
            'message' => 'Vœu introuvable ou non autorisé.'
        ], 404);
    }

    $voeu->delete();

    return response()->json([
        'message' => 'Vœu supprimé avec succès.',
    ], 200);
}

}