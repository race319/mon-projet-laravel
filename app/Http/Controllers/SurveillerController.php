<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surveiller;
use App\Models\User;
use App\Models\Creneau;
use OpenApi\Annotations as OA;

/**
  * @OA\Tag(
  *     name="Surveillances",
  *     description="Gestion des surveillances d'examen"
  * )
  */

class SurveillerController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/surveiller",
     *     tags={"Surveillances"},
     *     summary="Lister les surveillances",
     *     description="Retourne la liste des surveillances avec filtres optionnels",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="enseignant_id",
     *         in="query",
     *         required=false,
     *         description="ID de l’enseignant",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="creneau_id",
     *         in="query",
     *         required=false,
     *         description="ID du créneau",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des surveillances",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     )
     * )
     */
   
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
