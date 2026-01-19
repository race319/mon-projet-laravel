<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Creneau;

class CreneauCsvController extends Controller
{
    /**
     * Télécharger le CSV des créneaux
     */
    public function download()
    {
        $creneaux = Creneau::all();
        $filename = storage_path('app/public/creneaux.csv'); // chemin sûr

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_creneau',
            'date',
            'heure_debut',
            'heure_fin',
            'created_at',
            'updated_at',
            'actif'
        ], ';');

        // Données
        foreach ($creneaux as $c) {
            fputcsv($handle, [
                $c->id,
                $c->code_creneau,
                $c->date,
                $c->heure_debut,
                $c->heure_fin,
                $c->created_at,
                $c->updated_at,
                $c->actif
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des créneaux
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');

        // Ignorer la première ligne (header)
        fgetcsv($file, 1000, ';');

        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            Creneau::updateOrCreate(
                ['id' => $row[0]], // mettre à jour si l'id existe
                [
                    'code_creneau' => $row[1],
                    'date'         => $row[2],
                    'heure_debut'  => $row[3],
                    'heure_fin'    => $row[4],
                    'created_at'   => $row[5],
                    'updated_at'   => $row[6],
                    'actif'        => $row[7],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Créneaux importé avec succès !');
    }
}
