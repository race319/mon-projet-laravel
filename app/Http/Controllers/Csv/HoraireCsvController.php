<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Horaire;

class HoraireCsvController extends Controller
{
    public function download()
    {
        $horaires = Horaire::all();
        $filename = 'horaires.csv';

        $handle = fopen($filename, 'w+');

        fputcsv($handle, [
            'id',
            'jour',
            'creneau',
            'created_at',
            'updated_at'
        ], ';');

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

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:102400' // ✅ max ajouté
        ]);

        $path = $request->file('csv_file')->getRealPath();
        set_time_limit(0);           // ✅ Ajouté
        ini_set('memory_limit', '1024M'); // ✅ Ajouté

        $file = fopen($path, 'r');

        if (!$file) {
            throw new \Exception('Impossible d\'ouvrir le fichier.');
        }

        // ✅ Ajouté encodage
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

            \DB::disableQueryLog(); // ✅ Ajouté

            while (($row = fgetcsv($file, 0, ';')) !== false) {
                $csvLineNumber++;

                if (count($header) !== count($row)) {
                    \Log::warning("Ligne $csvLineNumber ignorée: nombre de colonnes incorrect");
                    continue;
                }

                $data = array_combine($header, $row);

                $batch[] = [
                    'jour'       => $data['jour'] ?? '',
                    'creneau'    => $data['creneau'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // ✅ Insert par lots de 1000
                if (count($batch) >= 1000) {
                    \DB::table('horaires')->insert($batch);
                    $importedCount += count($batch);
                    $batch = [];

                    if ($importedCount % 10000 === 0) {
                        gc_collect_cycles();
                    }
                }
            }

            // ✅ Dernier lot
            if (!empty($batch)) {
                \DB::table('horaires')->insert($batch);
                $importedCount += count($batch);
            }

            fclose($file);

            return back()->with('success', "Import terminé : $importedCount horaires importés.");

        } catch (\Exception $e) {
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }

            \Log::error('Import CSV horaires échoué', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', "Échec de l'import : " . $e->getMessage());
        }
    }
}