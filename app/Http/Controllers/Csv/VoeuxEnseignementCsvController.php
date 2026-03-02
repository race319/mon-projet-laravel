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
            'csv_file' => 'required|mimes:csv,txt|max:102400' // 100MB max
        ]);

        $path = $request->file('csv_file')->getRealPath();
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $file = fopen($path, 'r');

        if (!$file) {
            throw new \Exception('Impossible d\'ouvrir le fichier.');
        }

        if (!stream_filter_append($file, 'convert.iconv.ISO-8859-15/UTF-8')) {
            stream_filter_append($file, 'convert.iconv.WINDOWS-1252/UTF-8');
        }

        try {
            $header = fgetcsv($file, 0, ';');

            if (!$header) {
                fclose($file);
                throw new \Exception('Le fichier est vide ou corrompu.');
            }

            $importedCount = 0;
            $batch = [];
            $csvLineNumber = 0;

            \DB::disableQueryLog();

            while (($row = fgetcsv($file, 0, ';')) !== false) {
                $csvLineNumber++;

                if (count($header) !== count($row)) {
                    \Log::warning("Ligne $csvLineNumber ignorée: nombre de colonnes incorrect");
                    continue;
                }

                $data = array_combine($header, $row);

                $batch[] = [
                    'code_enseignant' => $data['code_enseignant'] ?? '',
                    'code_jour'       => $data['code_jour'] ?? '',
                    'code_seance'     => $data['code_seance'] ?? '',
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                // ✅ Insert par lots de 1000
                if (count($batch) >= 1000) {
                    \DB::table('voeux_enseignement')->insert($batch);
                    $importedCount += count($batch);
                    $batch = [];

                    if ($importedCount % 10000 === 0) {
                        gc_collect_cycles();
                    }
                }
            }

            // ✅ Dernier lot
            if (!empty($batch)) {
                \DB::table('voeux_enseignement')->insert($batch);
                $importedCount += count($batch);
            }

            fclose($file);

            return back()->with('success', "Import terminé : $importedCount vœux importés.");

        } catch (\Exception $e) {
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }

            \Log::error('Import CSV voeux_enseignement échoué', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', "Échec de l'import : " . $e->getMessage());
        }
    }
}