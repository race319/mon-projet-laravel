<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Horaire;

class HoraireCsvController extends Controller
{
    /**
     * Télécharger le CSV des horaires
     */
    public function download()
    {
        $horaires = Horaire::all();
        $filename = 'horaires.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'jour',
            'creneau',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($horaires as $h) {
            fputcsv($handle, [
                $h->id,
                $h->jour,
                $h->creneau,
                $h->created_at,
                $h->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des horaires
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
            Horaire::updateOrCreate(
                ['id' => $row[0]],
                [
                    'jour'       => $row[1],
                    'creneau'    => $row[2],
                    'created_at' => $row[3],
                    'updated_at' => $row[4],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Horaires importé avec succès !');
    }
}
