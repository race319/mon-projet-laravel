<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignement;

class EnseignantsCsvController extends Controller
{
    public function download()
    {
        $enseignants = \DB::table('enseignant')->get();
        $filename = 'enseignants.csv';

        $handle = fopen($filename, 'w+');

        // Entêtes CSV
        fputcsv($handle, [
            'code_enseignant',
            'charge_enseignement',
            'charge_surveillance',
            'created_at',
            'updated_at'
        ], ';');

        foreach ($enseignants as $e) {
            fputcsv($handle, [
                $e->code_enseignant,
                $e->charge_enseignement,
                $e->charge_surveillance,
                $e->created_at,
                $e->updated_at
            ], ';');
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

  public function upload(Request $request)
{
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt|max:102400'
    ]);

    $path = $request->file('csv_file')->getRealPath();
    set_time_limit(0);
    ini_set('memory_limit', '1024M');

    $file = fopen($path, 'r');
    if (!$file) {
        throw new \Exception("Impossible d'ouvrir le fichier.");
    }

    // Conversion encodage si nécessaire
    if (!stream_filter_append($file, 'convert.iconv.ISO-8859-15/UTF-8')) {
        stream_filter_append($file, 'convert.iconv.WINDOWS-1252/UTF-8');
    }

    try {
        $header = fgetcsv($file, 0, ';'); // lire le header
        if (!$header) {
            fclose($file);
            throw new \Exception("Le fichier est vide ou corrompu.");
        }

        $importedCount = 0;
        $batch = [];
        $csvLineNumber = 1;

        \DB::disableQueryLog();

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $csvLineNumber++;

            if (count($header) !== count($row)) {
                continue; // ignorer les lignes mal formées
            }

            $data = array_combine($header, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);

            $batch[] = [
                'code_enseignant' => $data['code_enseignant'] ?? null,
                'code_groupe'     => $data['code_groupe'] ?? null,
                'code_matiere'    => $data['code_matiere'] ?? null,
                'code_typeseance' => $data['code_typeseance'] ?? null,
                'date_seance'     => $data['date_seance'] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            // Batch de 1000 lignes
            if (count($batch) >= 1000) {
                \DB::table('enseignement')->insert($batch);
                $importedCount += count($batch);
                $batch = [];
                gc_collect_cycles();
            }
        }

        // Insérer le reste
        if (!empty($batch)) {
            \DB::table('enseignement')->insert($batch);
            $importedCount += count($batch);
        }

        fclose($file);

        return back()->with('success', "Import terminé : $importedCount lignes insérées directement, sans validation");

    } catch (\Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }

        \Log::error('Import CSV Enseignements échoué', [
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}