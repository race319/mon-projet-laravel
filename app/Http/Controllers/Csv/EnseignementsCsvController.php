<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enseignement;

class EnseignementsCsvController extends Controller
{
    public function download()
    {
        $enseignements = \DB::table('enseignement')->get();
        $filename = 'enseignements.csv';

        $handle = fopen($filename, 'w+');

       
        fputcsv($handle, [
            'code_enseignant',
            'code_groupe',
            'code_matiere',
            'code_typeseance',
            'date_seance',
            'created_at',
            'updated_at'
        ], ';');

        foreach ($enseignements as $e) {
            fputcsv($handle, [
                $e->code_enseignant,
                $e->code_groupe,
                $e->code_matiere,
                $e->code_typeseance,
                $e->date_seance,
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

    // ✅ NOUVEAU : Convertir encodage
    $content  = file_get_contents($path);
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    if ($encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        file_put_contents($path, $content);
    }

    // ✅ NOUVEAU : Détecter délimiteur
    $firstLine = substr($content, 0, strpos($content, "\n"));
    $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

    $file = fopen($path, 'r');

    if (!$file) {
        throw new \Exception("Impossible d'ouvrir le fichier.");
    }

    try {
        $header = fgetcsv($file, 0, $delimiter); // ✅ délimiteur dynamique

        if (!$header) {
            fclose($file);
            throw new \Exception("Le fichier est vide ou corrompu.");
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

        // ✅ Charger dictionnaire : normalized => vrai code BDD
        $validGroupes = \DB::table('groupes')
            ->pluck('code_groupe')
            ->filter()
            ->mapWithKeys(fn($v) => [$normalize($v) => $v])
            ->toArray();

        $validEnseignants = \DB::table('users')
            ->pluck('code_enseignant')
            ->filter()
            ->mapWithKeys(fn($v) => [$normalize($v) => $v])
            ->toArray();

        $validMatieres = \DB::table('matieres')
            ->pluck('code_matiere')
            ->filter()
            ->mapWithKeys(fn($v) => [$normalize($v) => $v])
            ->toArray();

        $importedCount = 0;
        $skippedCount  = 0;
        $batch         = [];
        $csvLineNumber = 1;
        $errors        = [];

        \DB::disableQueryLog();

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) { // ✅ délimiteur dynamique
            $csvLineNumber++;

            if (count($header) !== count($row)) {
                \Log::warning("Ligne $csvLineNumber ignorée: nombre de colonnes incorrect");
                $skippedCount++;
                continue;
            }

            $data = array_combine($header, $row);
            $data = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);

            $codeGroupe     = $data['code_groupe']     ?? '';
            $codeEnseignant = $data['code_enseignant'] ?? '';
            $codeMatiere    = $data['code_matiere']    ?? '';
            $codeTypeSeance = $data['code_typeseance'] ?? '';

            $codeGroupeNorm     = $normalize($codeGroupe);
            $codeEnseignantNorm = $normalize($codeEnseignant);
            $codeMatiereNorm    = $normalize($codeMatiere);

            if (!isset($validGroupes[$codeGroupeNorm])) {
                $errors[] = "Ligne $csvLineNumber: groupe '$codeGroupe' inexistant";
                $skippedCount++;
                continue;
            }

            if (!isset($validEnseignants[$codeEnseignantNorm])) {
                $errors[] = "Ligne $csvLineNumber: enseignant '$codeEnseignant' inexistant";
                $skippedCount++;
                continue;
            }

            if (!isset($validMatieres[$codeMatiereNorm])) {
                $errors[] = "Ligne $csvLineNumber: matière '$codeMatiere' inexistante";
                $skippedCount++;
                continue;
            }

            $vraiCodeGroupe     = $validGroupes[$codeGroupeNorm];
            $vraiCodeEnseignant = $validEnseignants[$codeEnseignantNorm];
            $vraiCodeMatiere    = $validMatieres[$codeMatiereNorm];

            $batch[] = [
                'code_enseignant' => $vraiCodeEnseignant,
                'code_groupe'     => $vraiCodeGroupe,
                'code_matiere'    => $vraiCodeMatiere,
                'code_typeseance' => $codeTypeSeance,
                'date_seance'     => $data['date_seance'] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (count($batch) >= 1000) {
                \DB::table('enseignement')->insert($batch);
                $importedCount += count($batch);
                $batch = [];
                if ($importedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        }

        if (!empty($batch)) {
            \DB::table('enseignement')->insert($batch);
            $importedCount += count($batch);
        }

        fclose($file);

        if (!empty($errors)) {
            \Log::warning('Import CSV: lignes ignorées', ['errors' => array_slice($errors, 0, 100)]);
        }

        $message = "Import terminé : $importedCount enseignements importés";
        if ($skippedCount > 0) {
            $message .= ", $skippedCount lignes ignorées (clés invalides)";
        }

        return back()->with('success', $message);

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
