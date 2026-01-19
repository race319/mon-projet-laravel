<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Csv\VoeuxExamensCsvController;
use App\Http\Controllers\Csv\SeanceCsvController;
use App\Http\Controllers\Csv\VoeuxEnseignementCsvController;
use App\Http\Controllers\Csv\AbsencesCsvController;
use App\Http\Controllers\Csv\EnseignantsCsvController;
use App\Http\Controllers\Csv\EnseignementsCsvController;
use App\Http\Controllers\Csv\GroupesCsvController;
use App\Http\Controllers\Csv\HoraireCsvController;
use App\Http\Controllers\Csv\InscriptionCsvController;
use App\Http\Controllers\Csv\MatiereCsvController;
use App\Http\Controllers\Csv\SalleCsvController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        // Si l'utilisateur est connecté, aller au dashboard
        return redirect()->route('admin.dashboard');
    }
    // Sinon, rediriger vers login
    return redirect()->route('login');
});


Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.web');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});
Route::post('/logout', function () {
    Auth::guard('web')->logout(); 
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::get('/admin/seances/csv', function () {
        return view('admin.seances_csv'); 
    })->name('seances.csv.page');
    
    Route::get('/admin/voeux-enseignement/csv', function () {
    return view('admin.voeux_enseignement_csv');
})->name('voeux_enseignement.csv.page');

Route::get('/admin/voeux-examen/csv', function () {
    return view('admin.voeux_examen_csv');
})->name('voeux_examen.csv.page');
Route::get('/absences/csv', function () {
    return view('admin.absences_csv');
})->name('absences.csv.page');

Route::get('/enseignants/csv', function () {
    return view('admin.enseignants_csv');
})->name('enseignants.csv.page');

Route::get('/enseignements/csv', function () {
    return view('admin.enseignements_csv');
})->name('enseignements.csv.page');

Route::get('/groupes/csv', function () {
    return view('admin.groupes_csv');
})->name('groupes.csv.page');

Route::get('/horaires/csv', function () {
    return view('admin.horaires_csv');
})->name('horaires.csv.page');

Route::get('/inscriptions/csv', function () {
    return view('admin.inscriptions_csv');
})->name('inscriptions.csv.page');

Route::get('/matieres/csv', function () {
    return view('admin.matieres_csv');
})->name('matieres.csv.page');

Route::get('/salles/csv', function () {
    return view('admin.salles_csv');
})->name('salles.csv.page');

Route::get('/creneaux/csv', function () {
    return view('admin.creneaux_csv');
})->name('creneaux.csv.page');


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/seances/csv/download', [SeanceCsvController::class, 'download'])->name('seances.csv.download');
    Route::post('/seances/csv/upload', [SeanceCsvController::class, 'upload'])->name('seances.csv.upload');

   Route::get('/admin/voeux-examens/csv/download', [VoeuxExamensCsvController::class, 'download'])
        ->name('voeux_examens.csv.download');

    Route::post('/admin/voeux-examens/csv/upload', [VoeuxExamensCsvController::class, 'upload'])
        ->name('voeux_examen.csv.upload');


    Route::get('/admin/voeux-enseignement/csv/download', [VoeuxEnseignementCsvController::class, 'download'])
        ->name('voeux_enseignement.csv.download');

    Route::post('/admin/voeux-enseignement/csv/upload', [VoeuxEnseignementCsvController::class, 'upload'])
        ->name('voeux_enseignement.csv.upload');
        Route::get('/absences/csv/download', [AbsencesCsvController::class, 'download'])
    ->name('absences.csv.download');

Route::post('/absences/csv/upload', [AbsencesCsvController::class, 'upload'])
    ->name('absences.csv.upload');
    Route::get('/enseignants/csv/download',
    [EnseignantsCsvController::class, 'download']
)->name('enseignants.csv.download');


Route::post('/enseignants/csv/upload',
    [EnseignantsCsvController::class, 'upload']
)->name('enseignants.csv.upload');

Route::get('/enseignements/csv/download',
    [EnseignementsCsvController::class, 'download']
)->name('enseignements.csv.download');


Route::post('/enseignements/csv/upload',
    [EnseignementsCsvController::class, 'upload']
)->name('enseignements.csv.upload');

Route::get('/groupes/csv/download',
    [GroupesCsvController::class, 'download']
)->name('groupes.csv.download');

Route::post('/groupes/csv/upload',
    [GroupesCsvController::class, 'upload']
)->name('groupes.csv.upload');

Route::get('/horaires/csv/download',
    [HoraireCsvController::class, 'download']
)->name('horaires.csv.download');

// Upload CSV
Route::post('/horaires/csv/upload',
    [HoraireCsvController::class, 'upload']
)->name('horaires.csv.upload');

Route::get('/inscriptions/csv/download',
    [InscriptionCsvController::class, 'download']
)->name('inscriptions.csv.download');

// Upload CSV
Route::post('/inscriptions/csv/upload',
    [InscriptionCsvController::class, 'upload']
)->name('inscriptions.csv.upload');

Route::get('/matieres/download', [MatiereCsvController::class, 'download'])
        ->name('matieres.csv.download');

    Route::post('/matieres/upload', [MatiereCsvController::class, 'upload'])
        ->name('matieres.csv.upload');


        Route::get('/salles/csv/download',
    [SalleCsvController::class, 'download']
)->name('salles.csv.download');

Route::post('/salles/csv/upload',
    [SalleCsvController::class, 'upload']
)->name('salles.csv.upload');

Route::get('/creneaux/csv/download',
    [CreneauCsvController::class, 'download']
)->name('creneaux.csv.download');

 
Route::post('/creneaux/csv/upload',
    [CreneauCsvController::class, 'upload']
)->name('creneaux.csv.upload');



});
