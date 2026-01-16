<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoeuxExamen;
use App\Models\Creneau;
use Illuminate\Support\Facades\Log;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Voeux Examen",
 *     description="Gestion des vœux de surveillance des examens"
 * )
 */




class VoeuxExamenController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/voeuxexa",
 *     tags={"Voeux Examen"},
 *     summary="Lister les vœux d'examen de l'enseignant connecté",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des vœux d'examen"
 *     )
 * )
 */
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

    /**
 * @OA\Post(
 *     path="/api/voeux-examen",
 *     tags={"Voeux Examen"},
 *     summary="Ajouter un vœu de surveillance",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"code_creneau"},
 *             @OA\Property(property="code_creneau", type="integer", example=4)
 *         )
 *     ),
 *     @OA\Response(response=201, description="Vœu ajouté"),
 *     @OA\Response(response=400, description="Charge dépassée ou vœu existant")
 * )
 */
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

    /**
 * @OA\Delete(
 *     path="/api/voeuxexa/{code_creneau}",
 *     tags={"Voeux Examen"},
 *     summary="Supprimer un vœu de surveillance",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="code_creneau",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Vœu supprimé"),
 *     @OA\Response(response=404, description="Vœu introuvable")
 * )
 */

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
    

   /**
 * @OA\Post(
 *     path="/api/voeux-examen/bulk",
 *     tags={"Voeux Examen"},
 *     summary="Remplacer tous les vœux de surveillance",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="voeux",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="code_creneau", type="integer", example=2)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Vœux mis à jour"),
 *     @OA\Response(response=400, description="Charge dépassée")
 * )
 */

   


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


    /**
 * @OA\Get(
 *     path="/api/enseignant/charge-surveillance",
 *     tags={"Voeux Examen"},
 *     summary="Récupérer la charge de surveillance",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Charge retournée"),
 *     @OA\Response(response=404, description="Enseignant non trouvé")
 * )
 */


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

   /**
 * @OA\Get(
 *     path="/api/creneaux",
 *     tags={"Créneaux"},
 *     summary="Lister les créneaux d'examen",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Liste des créneaux")
 * )
 */


    public function indexx()
    {
        $creneaux = Creneau::all(); 
        return response()->json([
            'success' => true,
            'data' => $creneaux
        ], 200);
    }
}

