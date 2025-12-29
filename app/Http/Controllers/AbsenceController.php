<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Seance;
use Illuminate\Http\Request;
use App\Models\Absence;
use App\Models\Enseignement;

class AbsenceController extends Controller
{
    public function getEnseignants() {
        $enseignants = User::where('role', 'enseignant')->select('id', 'name')->get();

        return response()->json($enseignants);
    }

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
    public function marquerAbsence(Request $request)
{
    $request->validate([
        'code_etudiant' => 'required|exists:users,id',
        'code_matiere' => 'required|exists:matieres,code_matiere',
        'code_enseignant' => 'required|exists:users,id',
        'seance' => 'required|integer',
        'statut' => 'required|in:Absent,Present',
        'justifie' => 'boolean',
        'date_absence' => 'required|date', // ✅ AJOUT : Date obligatoire
    ]);

    // ✅ SOLUTION : updateOrCreate pour éviter les doublons
    // Si l'absence existe déjà pour cet étudiant, cette matière, cette séance et cette date → UPDATE
    // Sinon → CREATE
    $absence = Absence::updateOrCreate(
        [
            // 🔑 Clés uniques pour identifier l'absence
            'code_etudiant' => $request->code_etudiant,
            'code_matiere' => $request->code_matiere,
            'seance' => $request->seance,
            'date_absence' => $request->date_absence, // ✅ Important pour éviter les doublons sur plusieurs jours
        ],
        [
            // 📝 Données à mettre à jour ou créer
            'code_enseignant' => $request->code_enseignant,
            'statut' => $request->statut,
            'justifie' => $request->justifie ?? 0,
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Absence enregistrée avec succès',
        'absence' => $absence
    ], 200); // ✅ 200 au lieu de 201 car peut être une mise à jour
}
    
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


}
