<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoeuxExamen;
use App\Models\Creneau;
use Illuminate\Support\Facades\Log;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Auth;




class VoeuxExamenController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $voeux = VoeuxExamen::with('creneau')
            ->where('code_enseignant', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $voeux
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'code_creneau' => 'required|integer|exists:creneau,code_creneau',
        ]);

        $user = auth()->user();
        $enseignant = $user->enseignant;

        if (!$enseignant) {
            return response()->json([
                'message' => 'Profil enseignant introuvable.'
            ], 404);
        }

        $heuresParCreneau = 2; // Chaque créneau = 2 heures
        $chargeTotale = $enseignant->charge_surveillance;

        // Calculer les heures déjà sélectionnées
        $heuresSelectionnees = VoeuxExamen::where('code_enseignant', $user->id)
            ->count() * $heuresParCreneau;

        // 🔍 DEBUG
        Log::info('Debug calcul heures surveillance:', [
            'user_id' => $user->id,
            'charge_totale' => $chargeTotale,
            'heures_deja_selectionnees' => $heuresSelectionnees,
            'heures_par_creneau' => $heuresParCreneau,
            'total_apres_ajout' => $heuresSelectionnees + $heuresParCreneau,
            'nombre_voeux_existants' => VoeuxExamen::where('code_enseignant', $user->id)->count(),
        ]);

        
        if (($heuresSelectionnees + $heuresParCreneau) > $chargeTotale) {
            $reste = $chargeTotale - $heuresSelectionnees;
            
            return response()->json([
                'message' => "Vous ne pouvez plus sélectionner ce créneau. Il vous reste $reste heure(s) à compléter.",
                'debug' => [
                    'charge_totale' => $chargeTotale,
                    'heures_selectionnees' => $heuresSelectionnees,
                    'heures_a_ajouter' => $heuresParCreneau,
                    'reste' => $reste
                ]
            ], 400);
        }

       
        $voeuExistant = VoeuxExamen::where('code_enseignant', $user->id)
            ->where('code_creneau', $request->code_creneau)
            ->first();

        if ($voeuExistant) {
            return response()->json([
                'message' => 'Vous avez déjà sélectionné ce créneau.'
            ], 400);
        }

        // Créer le vœu
        $voeu = VoeuxExamen::create([
            'code_enseignant' => $user->id,
            'code_creneau' => $request->code_creneau,
        ]);

        // Recalculer après ajout
        $heuresSelectionnees += $heuresParCreneau;
        $reste = $chargeTotale - $heuresSelectionnees;

        return response()->json([
            'message' => $reste > 0
                ? "Vœu ajouté avec succès. Il vous reste $reste heure(s) à sélectionner."
                : "Vœu ajouté avec succès. Vous avez atteint votre charge de surveillance.",
            'reste' => $reste,
            'data' => $voeu
        ], 201);
    }
    public function destroy($code_creneau)
    {
        $user = Auth::user();

        $voeu = VoeuxExamen::where('code_enseignant', $user->id)
            ->where('code_creneau', $code_creneau)
            ->first();

        if (!$voeu) {
            return response()->json([
                'success' => false,
                'message' => 'Vœu introuvable'
            ], 404);
        }

        $voeu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vœu supprimé avec succès'
        ]);
    }
    

   

   

    // 🔹 Mise à jour en bulk (modifier tous les vœux)
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'voeux' => 'required|array',
            'voeux.*.code_creneau' => 'required|integer|exists:creneau,code_creneau',
        ]);

        $user = $request->user();
        $heuresParCreneau = 2;
        $chargeTotale = $user->charge_surveillance ?? 10;

        $nouveauxVoeux = $request->voeux;
        $heuresNouveaux = count($nouveauxVoeux) * $heuresParCreneau;

        if ($heuresNouveaux > $chargeTotale) {
            return response()->json([
                'message' => "Le nombre de créneaux sélectionnés dépasse votre charge de surveillance ($chargeTotale heures)."
            ], 400);
        }

        // Supprimer les anciens vœux
        VoeuxExamen::where('code_enseignant', $user->id)->delete();

        // Créer les nouveaux vœux
        $voeuxCrees = [];
        foreach ($nouveauxVoeux as $voeu) {
            $voeuxCrees[] = VoeuxExamen::create([
                'code_enseignant' => $user->id,
                'code_creneau' => $voeu['code_creneau'],
            ]);
        }

        $reste = max(0, $chargeTotale - $heuresNouveaux);

        return response()->json([
            'message' => 'Vœux mis à jour avec succès.',
            'reste' => $reste,
            'data' => $voeuxCrees
        ], 200);
    }
    public function getChargeSurveillance(Request $request)
    {
        $user = $request->user();

        $enseignant = Enseignant::where('user_id', $user->id)->first();

        if (!$enseignant) {
            return response()->json([
                'message' => 'Enseignant non trouvé'
            ], 404);
        }

        return response()->json([
            'charge_surveillance' => $enseignant->charge_surveillance
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
}

