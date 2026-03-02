<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscrit;

class InscriptionCsvController extends Controller
{
    public function download()
    {
        $inscriptions = \DB::table('inscrit')->get();
        $filename = 'inscriptions.csv';

        $handle = fopen($filename, 'w+');

        
        fputcsv($handle, [
            'code_etudiant',
            'code_groupe',
            'created_at',
            'updated_at'
        ], ';');

        foreach ($inscriptions as $i) {
            fputcsv($handle, [
                $i->code_etudiant,
                $i->code_groupe,
                $i->created_at,
                $i->updated_at
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

        // ✅ Fonction de normalisation complète
        $normalize = function(string $value): string {
            $value = trim($value);
            $value = mb_strtolower($value, 'UTF-8');
            $value = preg_replace('/\s+/', ' ', $value);
            $value = str_replace(
                ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç'],
                ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c'],
                $value
            );
            return $value;
        };

        // ✅ Dictionnaire groupes : normalized => vrai code BDD
        $validGroupes = \DB::table('groupes')
            ->pluck('code_groupe')
            ->filter()
            ->mapWithKeys(fn($v) => [$normalize($v) => $v])
            ->toArray();

        $importedCount = 0;
        $skippedCount  = 0;
        $batch         = [];
        $csvLineNumber = 0;
        $errors        = [];

        \DB::disableQueryLog();

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $csvLineNumber++;

            if (count($header) !== count($row)) {
                \Log::warning("Ligne $csvLineNumber ignorée: nombre de colonnes incorrect");
                $skippedCount++;
                continue;
            }

            $data = array_combine($header, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);

            $codeEtudiant = $data['code_etudiant'] ?? '';
            $codeGroupe   = $data['code_groupe']   ?? '';

            // ✅ Normaliser code_groupe
            $codeGroupeNorm = $normalize($codeGroupe);

            // ✅ Validation code_groupe
            if (!isset($validGroupes[$codeGroupeNorm])) {
                $errors[] = "Ligne $csvLineNumber: groupe '$codeGroupe' inexistant";
                $skippedCount++;
                continue;
            }

            // ✅ Récupérer le VRAI code groupe BDD
            $vraiCodeGroupe = $validGroupes[$codeGroupeNorm];

            $batch[] = [
                'code_etudiant' => $codeEtudiant,
                'code_groupe'   => $vraiCodeGroupe, // ✅ Vrai code BDD
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            if (count($batch) >= 1000) {
                \DB::table('inscrit')->insert($batch);
                $importedCount += count($batch);
                $batch = [];
                if ($importedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        }

        if (!empty($batch)) {
            \DB::table('inscrit')->insert($batch);
            $importedCount += count($batch);
        }

        fclose($file);

        if (!empty($errors)) {
            \Log::warning('Import CSV Inscriptions: lignes ignorées', ['errors' => array_slice($errors, 0, 100)]);
        }

        $message = "Import terminé : $importedCount inscriptions importées.";
        if ($skippedCount > 0) {
            $message .= ", $skippedCount lignes ignorées (clés invalides)";
        }

        return back()->with('success', $message);

    } catch (\Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }

        \Log::error('Import CSV Inscriptions échoué', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}