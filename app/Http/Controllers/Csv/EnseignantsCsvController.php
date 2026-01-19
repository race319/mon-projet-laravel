<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignant;

class EnseignantsCsvController extends Controller
{
    /**
     * Télécharger le CSV des enseignants
     */
    public function download()
    {
        $enseignants = Enseignant::all();
        $filename = 'enseignants.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'user_id',
            'charge_enseignement',
            'charge_surveillance',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($enseignants as $e) {
            fputcsv($handle, [
                $e->user_id,
                $e->charge_enseignement,
                $e->charge_surveillance,
                $e->created_at,
                $e->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des enseignants
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
            Enseignant::updateOrCreate(
                ['user_id' => $row[0]],
                [
                    'charge_enseignement' => $row[1],
                    'charge_surveillance' => $row[2],
                    'created_at'          => $row[3],
                    'updated_at'          => $row[4],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Enseignants importé avec succès !');
    }
}
