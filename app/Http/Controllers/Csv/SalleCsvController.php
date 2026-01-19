<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salle;

class SalleCsvController extends Controller
{
    /**
     * Télécharger le CSV des salles
     */
    public function download()
    {
        $salles = Salle::all();
        $filename = storage_path('app/public/salles.csv'); // chemin sûr

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_salle',
            'nom_salle',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($salles as $s) {
            fputcsv($handle, [
                $s->id,
                $s->code_salle,
                $s->nom_salle,
                $s->created_at,
                $s->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des salles
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
            Salle::updateOrCreate(
                ['id' => $row[0]], // mettre à jour si l'id existe
                [
                    'code_salle' => $row[1],
                    'nom_salle'  => $row[2],
                    'created_at' => $row[3],
                    'updated_at' => $row[4],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Salles importé avec succès !');
    }
}
