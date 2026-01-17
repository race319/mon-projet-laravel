<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AbsenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VoeuxEnseignementController;  
use App\Http\Controllers\VoeuxExamenController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\SurveillerController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('auth')->group(function () {
    Route::post('/loginn', [AuthController::class, 'login']); 
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/voeux', [VoeuxEnseignementController::class, 'store']);
    Route::put('/voeux/update', [VoeuxEnseignementController::class, 'bulkUpdate']);
    Route::get('/voeux', [VoeuxEnseignementController::class, 'index']);
    Route::get('voeuxexa', [VoeuxExamenController::class, 'index']);
    Route::delete('voeuxexa/{code_creneau}', [VoeuxExamenController::class, 'destroy']);
    Route::get('/enseignant/charge-surveillance', [VoeuxExamenController::class, 'getChargeSurveillance']);
    Route::post('/voeuxexa', [VoeuxExamenController::class, 'store']);
   
    Route::post('/voeux-examen/bulk', [VoeuxExamenController::class, 'bulkUpdate']);
    Route::get('creneaux', [VoeuxExamenController::class, 'indexx']);
    Route::post('/seances', [SeanceController::class, 'filter']);
    Route::put('/seances/{id}/etat', [SeanceController::class, 'updateEtat']);
    Route::get('/enseignants', [AbsenceController::class, 'getEnseignants']);
    Route::get('/absences/enseignant/{id}', [AbsenceController::class, 'getAbsencesByEnseignant']);
    Route::get('/enseignant/{code_enseignant}/groupes', [EnseignantController::class, 'getGroupes']);
    Route::get('/groupe/{code_groupe}/etudiants', [GroupeController::class, 'getEtudiants']);
    Route::get('/groupe/{code_groupe}/matieres', [GroupeController::class, 'getMatieres']);
    Route::post('/absence', [AbsenceController::class, 'marquerAbsence']); // POST pour créer
Route::put('/absences/{id}', [AbsenceController::class, 'updateAbsence']);
Route::get('/enseignant/charge', [EnseignantController::class, 'getCharge']);
Route::get('/voeux-enseignement', [VoeuxEnseignementController::class, 'index']);
Route::delete('/voeux-enseignement/{id}', [VoeuxEnseignementController::class, 'destroy']);
Route::get('/seances/config', function() {
    return response()->json([
        'absence_modification_seconds' => config('seances.absence_modification_seconds')
    ]);
});

Route::get('/absences/etudiant/{code_etudiant}/matiere/{code_matiere}', [AbsenceController::class, 'nombreAbsencesEtudiant']);
Route::post('/absences/elimination',[AbsenceController::class, 'changerElimination']);




Route::get('/surveiller', [SurveillerController::class, 'index']);
Route::put('/surveillances/{id}/qualite', [SurveillerController::class, 'updateQualite']);





});
