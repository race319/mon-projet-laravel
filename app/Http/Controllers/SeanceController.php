<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Carbon\Carbon;




/**
 * @OA\Tag(
 *     name="Séances",
 *     description="Gestion des séances"
 * )
 */

class SeanceController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/seances",
     *     tags={"Séances"},
     *     summary="Filtrer les séances par date et heure",
     *     description="Retourne les séances d’une date donnée avec une heure optionnelle",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date_seance"},
     *             @OA\Property(
     *                 property="date_seance",
     *                 type="string",
     *                 format="date",
     *                 example="2026-01-15"
     *             ),
     *             @OA\Property(
     *                 property="heure_seance",
     *                 type="string",
     *                 example="08:00-10:00"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des séances",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="selected_date", type="string", example="2026-01-15"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     )
     * )
     */
    
    public function filter(Request $request)
    {
        $request->validate([
            'date_seance' => 'required|date',
            'heure_seance' => 'nullable|string' 
        ]);

        $query = Seance::with(['enseignant', 'salle', 'groupe'])
            ->where('date_seance', $request->date_seance)
            ->where('code_suveillance', auth()->id());

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
    /**
     * @OA\Put(
     *     path="/api/seances/{id}/etat",
     *     tags={"Séances"},
     *     summary="Modifier l’état d’une séance",
     *     description="Met à jour l’état d’une séance (0 = non faite, 1 = faite)",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la séance",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"etat"},
     *             @OA\Property(
     *                 property="etat",
     *                 type="integer",
     *                 enum={0,1},
     *                 example=1
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Etat modifié avec succès"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Séance introuvable"
     *     )
     * )
     */

  public function updateEtat(Request $request, $id)
{
    
    $request->validate([
        'etat' => 'required|in:0,1' 
    ]);

    $seance = Seance::findOrFail($id);

   
    $duration = config('seances.absence_modification_seconds'); // durée en secondes
    if ($seance->locked_at) {
        $limit = Carbon::parse($seance->locked_at)->addSeconds($duration);

        if (now()->greaterThan($limit)) {
            return response()->json([
                'success' => false,
                'message' => 'Modification impossible : délai dépassé'
            ], 403);
        }
    } else {
       
        $seance->locked_at = now();
    }

    $seance->etat = $request->etat;
    $seance->code_suveillance = auth()->id();
    $seance->save();

    return response()->json([
        'success' => true,
        'message' => 'Etat modifié avec succès',
        'data' => $seance->load('surveillant') 
    ], 200);
}
}
