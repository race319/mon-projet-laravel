<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absence;

class AbsencesCsvController extends Controller
{
    /**
     * Télécharger le CSV des absences
     */
    public function download()
    {
        $absences = Absence::all();
        $filename = 'absences.csv';

        $handle = fopen($filename, 'w+');

        // En-tête CSV
        fputcsv($handle, [
            'id',
            'code_etudiant',
            'code_matiere',
            'code_enseignant',
            'seance',
            'statut',
            'justifie',
            'elimination',
            'date_absence',
            'created_at',
            'updated_at'
        ], ';');

        // Données
        foreach ($absences as $a) {
            fputcsv($handle, [
                $a->id,
                $a->code_etudiant,
                $a->code_matiere,
                $a->code_enseignant,
                $a->seance,
                $a->statut,
                $a->justifie,
                $a->elimination,
                $a->date_absence,
                $a->created_at,
                $a->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Importer le CSV des absences
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
            Absence::updateOrCreate(
                ['id' => $row[0]],
                [
                    'code_etudiant'   => $row[1],
                    'code_matiere'    => $row[2],
                    'code_enseignant' => $row[3],
                    'seance'          => $row[4],
                    'statut'          => $row[5],
                    'justifie'        => $row[6],
                    'elimination'     => $row[7],
                    'date_absence'    => $row[8],
                    'created_at'      => $row[9],
                    'updated_at'      => $row[10],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Absences importé avec succès !');
    }
}
