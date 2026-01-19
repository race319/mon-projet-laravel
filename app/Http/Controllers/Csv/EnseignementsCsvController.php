<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignement;

class EnseignementsCsvController extends Controller
{
    /**
     * Télécharger le CSV des enseignements
     */
    public function download()
    {
        $enseignements = Enseignement::all();
        $filename = 'enseignements.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_enseignant',
            'code_groupe',
            'code_matiere',
            'nature_enseignement',
            'date_seance',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($enseignements as $e) {
            fputcsv($handle, [
                $e->id,
                $e->code_enseignant,
                $e->code_groupe,
                $e->code_matiere,
                $e->nature_enseignement,
                $e->date_seance,
                $e->created_at,
                $e->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des enseignements
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
            Enseignement::updateOrCreate(
                ['id' => $row[0]],
                [
                    'code_enseignant'    => $row[1],
                    'code_groupe'        => $row[2],
                    'code_matiere'       => $row[3],
                    'nature_enseignement'=> $row[4],
                    'date_seance'        => $row[5],
                    'created_at'         => $row[6],
                    'updated_at'         => $row[7],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Enseignements importé avec succès !');
    }
}
