<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoeuxExamen;

class VoeuxExamensCsvController extends Controller
{
    
    public function download()
    {
        $voeux = VoeuxExamen::all();
        $filename = 'voeux_examens.csv';

        $handle = fopen($filename, 'w+');

        
        fputcsv($handle, [
            'id',
            'code_enseignant',
            'code_creneau',
            'created_at',
            'updated_at'
        ], ';');

        
        foreach ($voeux as $v) {
            fputcsv($handle, [
                $v->id,
                $v->code_enseignant,
                $v->code_creneau,
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
            VoeuxExamen::updateOrCreate(
                ['id' => $row[0]],
                [
                    'code_enseignant' => $row[1],
                    'code_creneau' => $row[2],
                    'created_at' => $row[3],
                    'updated_at' => $row[4],
                ]
            );
        }

        fclose($file);

        return back()->with('success', 'CSV Vœux Examens importé avec succès !');
    }
}
