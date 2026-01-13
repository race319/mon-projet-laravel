<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Seance;
use Illuminate\Http\Request;
use App\Models\Absence;
use App\Models\Matiere;
use App\Models\Enseignement;
use OpenApi\Annotations as OA;



/**
 * @OA\Tag(
 *     name="Absences",
 *     description="Gestion des absences des étudiants et enseignants"
 * )
 */


class AbsenceController extends Controller



{

    /**
     * @OA\Get(
     *     path="/api/enseignants",
     *     tags={"Absences"},
     *     summary="Récupérer la liste des enseignants",
     *     description="Retourne tous les enseignants avec leur id et nom",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des enseignants",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe")
     *             )
     *         )
     *     )
     * )
     */
    public function getEnseignants() {
        $enseignants = User::where('role', 'enseignant')->select('id', 'name')->get();

        return response()->json($enseignants);
    }

    /**
     * @OA\Get(
     *     path="/api/absences/enseignant/{id}",
     *     tags={"Absences"},
     *     summary="Récupérer les absences d'un enseignant",
     *     description="Retourne les absences (séances non réalisées) d'un enseignant",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'enseignant",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des absences",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="enseignant_id", type="integer", example=1),
     *             @OA\Property(property="total_absences", type="integer", example=3),
     *             @OA\Property(
     *                 property="dates_absences",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date_seance", type="string", example="2026-01-10"),
     *                     @OA\Property(property="heure_seance", type="string", example="08:00-10:00"),
     *                     @OA\Property(property="nature", type="string", example="Cours")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function getAbsencesByEnseignant($id) {
        $absences = Seance::where('code_enseignant', $id)
            ->where('etat', 0) 
            ->select('date_seance', 'heure_seance', 'nature')
            ->get();

        $totalAbsences = $absences->count();

        return response()->json([
            'enseignant_id' => $id,
            'total_absences' => $totalAbsences,
            'dates_absences' => $absences
        ]);
    }



    /**
     * @OA\Post(
     *     path="/api/absence",
     *     tags={"Absences"},
     *     summary="Marquer une absence",
     *     description="Créer ou mettre à jour une absence pour un étudiant",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code_etudiant", type="integer", example=10),
     *             @OA\Property(property="code_matiere", type="integer", example=5),
     *             @OA\Property(property="code_enseignant", type="integer", example=2),
     *             @OA\Property(property="seance", type="integer", example=1),
     *             @OA\Property(property="statut", type="string", enum={"Absent","Present"}, example="Absent"),
     *             @OA\Property(property="justifie", type="boolean", example=false),
     *             @OA\Property(property="date_absence", type="string", format="date", example="2026-01-10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence enregistrée",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Absence enregistrée avec succès"),
     *             @OA\Property(property="absence", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function marquerAbsence(Request $request)
{
    $request->validate([
        'code_etudiant' => 'required|exists:users,id',
        'code_matiere' => 'required|exists:matieres,code_matiere',
        'code_enseignant' => 'required|exists:users,id',
        'seance' => 'required|integer',
        'statut' => 'required|in:Absent,Present',
        'justifie' => 'boolean',
        'date_absence' => 'required|date', 
    ]);

    
    $absence = Absence::updateOrCreate(
        [
            
            'code_etudiant' => $request->code_etudiant,
            'code_matiere' => $request->code_matiere,
            'seance' => $request->seance,
            'date_absence' => $request->date_absence, 
        ],
        [
            'code_enseignant' => $request->code_enseignant,
            'statut' => $request->statut,
            'justifie' => $request->justifie ?? 0,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Absence enregistrée avec succès',
        'absence' => $absence
    ], 200); 
}

/**
     * @OA\Put(
     *     path="/api/absences/{id}",
     *     tags={"Absences"},
     *     summary="Mettre à jour une absence",
     *     description="Modifier le statut ou la justification d'une absence existante",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'absence",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="statut", type="string", enum={"Absent","Present"}, example="Present"),
     *             @OA\Property(property="justifie", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Absence mise à jour",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Absence mise à jour avec succès"),
     *             @OA\Property(property="absence", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Absence introuvable"
     *     )
     * )
     */
    
public function updateAbsence(Request $request, $id)
{
    $absence = Absence::find($id);

    if (!$absence) {
        return response()->json(['message' => 'Absence introuvable'], 404);
    }

    $request->validate([
        'statut' => 'in:Absent,Present',
        'justifie' => 'boolean'
    ]);

    $absence->update([
        'statut' => $request->statut ?? $absence->statut,
        'justifie' => $request->justifie ?? $absence->justifie,
    ]);

    return response()->json([
        'message' => 'Absence mise à jour avec succès',
        'absence' => $absence
    ], 200);
}
public function nombreAbsencesEtudiant($codeEtudiant, $codeMatiere)
{
    $nombreAbsences = Absence::nombreAbsences($codeEtudiant, $codeMatiere);
    $estElimine = Absence::estElimine($codeEtudiant, $codeMatiere);

    $etudiant = User::find($codeEtudiant);
    $matiere = Matiere::find($codeMatiere);

    return response()->json([
        'success' => true,
        'etudiant' => [
            'code' => $codeEtudiant,
            'nom' => $etudiant->name ?? 'N/A',
        ],
        'matiere' => [
            'code' => $codeMatiere,
            'nom' => $matiere->nom_matiere ?? 'N/A',
        ],
        'nombre_absences' => $nombreAbsences,
        'est_elimine' => $estElimine,
        'statut_elimination' => $estElimine ? 'Éliminé' : 'Non éliminé'
    ], 200);
}


public function changerElimination(Request $request)
{
    \Log::info('Données reçues:', $request->all());

    $request->validate([
        'code_etudiant' => 'required',
        'code_matiere' => 'required',
        'elimination' => 'required|in:0,1', 
    ]);

    Absence::changerElimination(
        $request->code_etudiant, 
        $request->code_matiere,
        $request->elimination
    );

    $message = $request->elimination == 1 
        ? 'Étudiant marqué comme éliminé' 
        : 'Élimination annulée';

    return response()->json([
        'success' => true,
        'message' => $message
    ]);
}

public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,enseignant,etudiant',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user'    => $user,
        ], 201);
    }




}
