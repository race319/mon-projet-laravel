<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Csv\VoeuxExamensCsvController;
use App\Http\Controllers\Csv\SeanceCsvController;
use App\Http\Controllers\Csv\VoeuxEnseignementCsvController;



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
});
