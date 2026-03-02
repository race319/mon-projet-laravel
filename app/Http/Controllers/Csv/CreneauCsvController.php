<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Creneau;

class CreneauCsvController extends Controller
{
    public function download()
    {
        $creneaux = Creneau::all();
        $filename = 'creneaux.csv';

        $handle = fopen($filename, 'w+');

        fputcsv($handle, [
            'code_creneau',
            'date',
            'heure_debut',
            'heure_fin',
            'created_at',
            'updated_at'
        ], ';');

        foreach ($creneaux as $c) {
            fputcsv($handle, [
                $c->code_creneau,
                $c->date,
                $c->heure_debut,
                $c->heure_fin,
                $c->created_at,
                $c->updated_at
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

        // ✅ Dictionnaire créneaux : normalized => vrai code BDD
        $validCreneaux = \DB::table('creneau')
            ->pluck('code_creneau')
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

            $codeCreneau = $data['code_creneau'] ?? '';

            // ✅ Normaliser code_creneau
            $codeCreneauNorm = $normalize($codeCreneau);

            // ✅ Validation : si le créneau existe déjà → ignorer
            if (isset($validCreneaux[$codeCreneauNorm])) {
                $errors[] = "Ligne $csvLineNumber: créneau '$codeCreneau' existe déjà";
                $skippedCount++;
                continue;
            }

            $batch[] = [
                'code_creneau' => $codeCreneau, // ✅ Code original du CSV
                'date'         => $data['date']        ?? '',
                'heure_debut'  => $data['heure_debut'] ?? '',
                'heure_fin'    => $data['heure_fin']   ?? '',
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            // ✅ Ajouter au dictionnaire pour éviter doublons dans le CSV lui-même
            $validCreneaux[$codeCreneauNorm] = $codeCreneau;

            if (count($batch) >= 1000) {
                \DB::table('creneau')->insert($batch);
                $importedCount += count($batch);
                $batch = [];

                if ($importedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        }

        if (!empty($batch)) {
            \DB::table('creneau')->insert($batch);
            $importedCount += count($batch);
        }

        fclose($file);

        if (!empty($errors)) {
            \Log::warning('Import CSV Créneaux: lignes ignorées', ['errors' => array_slice($errors, 0, 100)]);
        }

        $message = "Import terminé : $importedCount créneaux importés.";
        if ($skippedCount > 0) {
            $message .= ", $skippedCount lignes ignorées (doublons)";
        }

        return back()->with('success', $message);

    } catch (\Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }

        \Log::error('Import CSV créneaux échoué', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}