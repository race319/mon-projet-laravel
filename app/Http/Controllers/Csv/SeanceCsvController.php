<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seance;

class SeanceCsvController extends Controller
{
    public function download()
    {
        $seances = Seance::all();
        $filename = 'seances.csv';

        $handle = fopen($filename, 'w+');

        // ENTÊTE CSV
        fputcsv($handle, [
            'id',
            'date_seance',
            'heure_seance',
            'numero_seance',
            'code_salle',
            'nature',
            'nb_seances',
            'code_enseignant',
            'code_groupe',
            'code_matiere',      // ✅ AJOUT
            'etat',
            'locked_at',
            'updated_at',
            'code_suveillance',
            'created_at'
        ], ';');

        // DONNÉES
        foreach ($seances as $s) {
            fputcsv($handle, [
                $s->id,
                $s->date_seance,
                $s->heure_seance,
                $s->numero_seance,
                $s->code_salle,
                $s->nature,
                $s->nb_seances,
                $s->code_enseignant,
                $s->code_groupe,
                $s->code_matiere,   // ✅ BON ENDROIT
                $s->etat,
                $s->locked_at,
                $s->updated_at,
                $s->code_suveillance,
                $s->created_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $file = fopen($path, 'r');

        // Lire l'entête
        $header = fgetcsv($file, 1000, ';');

        // Lire les lignes
        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            Seance::updateOrCreate(
                ['id' => $row[0]],
                [
                    'date_seance'      => $row[1],
                    'heure_seance'     => $row[2],
                    'numero_seance'    => $row[3],
                    'code_salle'       => $row[4],
                    'nature'           => $row[5],
                    'nb_seances'       => $row[6],
                    'code_enseignant'  => $row[7],
                    'code_groupe'      => $row[8],
                    'code_matiere'     => $row[9],   // ✅ AJOUT
                    'etat'             => $row[10],
                    'locked_at'        => $row[11],
                    'updated_at'       => $row[12],
                    'code_suveillance' => $row[13],
                    'created_at'       => $row[14],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV importé avec succès !');
    }
}
