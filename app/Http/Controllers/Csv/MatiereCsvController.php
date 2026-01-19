<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matiere;

class MatiereCsvController extends Controller
{
    /**
     * Télécharger le CSV des matières
     */
    public function download()
    {
        $matieres = Matiere::all();
        $filename = 'matieres.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_matiere',
            'nom_matiere',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($matieres as $m) {
            fputcsv($handle, [
                $m->id,
                $m->code_matiere,
                $m->nom_matiere,
                $m->created_at,
                $m->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des matières
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
            Matiere::updateOrCreate(
                ['id' => $row[0]], // utilise l'id pour mettre à jour si existant
                [
                    'code_matiere' => $row[1],
                    'nom_matiere'  => $row[2],
                    'created_at'   => $row[3],
                    'updated_at'   => $row[4],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Matières importé avec succès !');
    }
}
