<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscrit;

class InscriptionCsvController extends Controller
{
    /**
     * Télécharger le CSV des inscriptions
     */
    public function download()
    {
        $inscriptions = Inscrit::all();
        $filename = 'inscriptions.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_etudiant',
            'code_groupe',
            'date_inscription',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($inscriptions as $i) {
            fputcsv($handle, [
                $i->id,
                $i->code_etudiant,
                $i->code_groupe,
                $i->date_inscription,
                $i->created_at,
                $i->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des inscriptions
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
            Inscription::updateOrCreate(
                ['id' => $row[0]],
                [
                    'code_etudiant'   => $row[1],
                    'code_groupe'     => $row[2],
                    'date_inscription'=> $row[3],
                    'created_at'      => $row[4],
                    'updated_at'      => $row[5],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Inscriptions importé avec succès !');
    }
}
