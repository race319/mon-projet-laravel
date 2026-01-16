<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoeuxEnseignement;


class VoeuxEnseignementCsvController extends Controller
{
    
    public function download()
    {
        $voeux = VoeuxEnseignement::all();
        $filename = 'voeux_enseignement.csv';

        $handle = fopen($filename, 'w+');

        
        fputcsv($handle, [
            'id',
            'code_enseignant',
            'code_jour',
            'code_seance',
            'created_at',
            'updated_at'
        ], ';');

        
        foreach ($voeux as $v) {
            fputcsv($handle, [
                $v->id,
                $v->code_enseignant,
                $v->code_jour,
                $v->code_seance,
                $v->created_at,
                $v->updated_at
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

        
        fgetcsv($file, 1000, ';');

        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            VoeuxEnseignement::updateOrCreate(
                ['id' => $row[0]],
                [
                    'code_enseignant' => $row[1],
                    'code_jour' => $row[2],
                    'code_seance' => $row[3],
                    'created_at' => $row[4],
                    'updated_at' => $row[5],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Vœux Enseignement importé avec succès !');
    }
}
