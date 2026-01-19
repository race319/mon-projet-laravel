<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Groupe;

class GroupesCsvController extends Controller
{
    /**
     * Télécharger le CSV des groupes
     */
    public function download()
    {
        $groupes = Groupe::all();
        $filename = 'groupes.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'code_groupe',
            'nom_groupe',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($groupes as $g) {
            fputcsv($handle, [
                $g->code_groupe,
                $g->nom_groupe,
                $g->created_at,
                $g->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des groupes
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
            Groupe::updateOrCreate(
                ['code_groupe' => $row[0]],
                [
                    'nom_groupe' => $row[1],
                    'created_at' => $row[2],
                    'updated_at' => $row[3],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Groupes importé avec succès !');
    }
}
