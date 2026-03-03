<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Matiere;

class MatiereCsvController extends Controller
{
    public function download()
    {
        $matieres = Matiere::all();
        $filename = 'matieres.csv';

        $handle = fopen($filename, 'w+');

        fputcsv($handle, [
            'code_matiere',
            'nom_matiere',
            'created_at',
            'updated_at'
        ], ';');

        foreach ($matieres as $m) {
            fputcsv($handle, [
                $m->code_matiere,
                $m->nom_matiere,
                $m->created_at,
                $m->updated_at
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

    // ✅ Convertir fichier en UTF-8
    $content  = file_get_contents($path);
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    \Log::info("Encodage détecté: " . $encoding);

    if ($encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        file_put_contents($path, $content);
    }

    $file = fopen($path, 'r');

    if (!$file) {
        throw new \Exception('Impossible d\'ouvrir le fichier.');
    }

    // ✅ Debug première ligne
    $firstLine = fgets($file);
    rewind($file);
    \Log::info("Première ligne brute: " . $firstLine);
    \Log::info("Nombre virgules: " . substr_count($firstLine, ','));
    \Log::info("Nombre points-virgules: " . substr_count($firstLine, ';'));

    // ✅ Détecter délimiteur
    $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
    \Log::info("Délimiteur détecté: " . $delimiter);

    try {
        $rawHeader = fgetcsv($file, 0, $delimiter);

        if (!$rawHeader) {
            fclose($file);
            throw new \Exception('Le fichier est vide ou corrompu.');
        }

        $header = array_map('trim', $rawHeader);
        \Log::info("Header colonnes: " . implode(' | ', $header));
        \Log::info("Nombre colonnes: " . count($header));

        $importedCount = 0;
        $batch         = [];
        $csvLineNumber = 0;

        \DB::disableQueryLog();

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $csvLineNumber++;

            if (count($header) !== count($row)) {
                \Log::warning("Ligne $csvLineNumber ignorée: " . count($header) . " colonnes attendues, " . count($row) . " trouvées");
                continue;
            }

            $row  = array_map('trim', $row);
            $data = array_combine($header, $row);

            // ✅ Debug première ligne de données
            if ($csvLineNumber === 1) {
                \Log::info("Première ligne données: " . json_encode($data));
            }

            $batch[] = [
                'code_matiere' => $data['code_matiere'] ?? '',
                'nom_matiere'  => $data['nom_matiere']  ?? '',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if (count($batch) >= 1000) {
                \DB::table('matieres')->insertOrIgnore($batch);
                $importedCount += count($batch);
                $batch = [];

                if ($importedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        }

        if (!empty($batch)) {
            \DB::table('matieres')->insertOrIgnore($batch);
            $importedCount += count($batch);
        }

        fclose($file);

        return back()->with('success', "Import terminé : $importedCount matières importées.");

    } catch (\Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }

        \Log::error('Import CSV Matières échoué', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}