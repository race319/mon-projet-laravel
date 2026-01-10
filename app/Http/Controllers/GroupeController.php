<?php

namespace App\Http\Controllers;

use App\Models\Inscrit;
use App\Models\Enseignement;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Groupes",
 *     description="Gestion des groupes, récupération des étudiants et matières"
 * )
 */

class GroupeController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/groupe/{code_groupe}/etudiants",
     *     tags={"Groupes"},
     *     summary="Récupérer les étudiants d'un groupe",
     *     description="Retourne tous les étudiants inscrits dans un groupe donné",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="code_groupe",
     *         in="path",
     *         description="Code du groupe",
     *         required=true,
     *         @OA\Schema(type="string", example="G101")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des étudiants",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun étudiant trouvé pour ce groupe"
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/groupe/{code_groupe}/matieres",
     *     tags={"Groupes"},
     *     summary="Récupérer les matières d'un groupe",
     *     description="Retourne toutes les matières associées à un groupe",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="code_groupe",
     *         in="path",
     *         description="Code du groupe",
     *         required=true,
     *         @OA\Schema(type="string", example="G101")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des matières",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucune matière trouvée pour ce groupe"
     *     )
     * )
     */

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