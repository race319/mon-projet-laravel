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

        
        fputcsv($handle, [
            'id',
            'date_seance',
            'code_jour',
            'numero_seance',
            'code_salle',
            'code_typeseance',
            'nb_seances',
            'code_enseignant',
            'code_groupe',
            'code_effectue',
            'code_suveillance',
            'locked_at',
            'created_at',
            'updated_at'
        ], ';');

        
        foreach ($seances as $s) {
            fputcsv($handle, [
                $s->id,
                $s->date_seance,
                $s->code_jour,
                $s->numero_seance,
                $s->code_salle,
                $s->code_typeseance,
                $s->nb_seances,
                $s->code_enseignant,
                $s->code_groupe,
                $s->code_effectue,
                $s->code_suveillance,
                $s->locked_at,
                $s->created_at,
                $s->updated_at
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
    $content = file_get_contents($path);
    $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        file_put_contents($path, $content);
        \Log::info("Fichier converti de $encoding vers UTF-8");
    }

    $file = fopen($path, 'r');
    if (!$file) {
        throw new \Exception('Impossible d\'ouvrir le fichier.');
    }

    try {
        // ✅ Détecter le délimiteur
        $firstLine = fgets($file);
        rewind($file);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        \Log::info("Délimiteur détecté: '$delimiter'");

        // ✅ Lire et nettoyer l'en-tête
        $rawHeader = fgetcsv($file, 0, $delimiter);
        if (!$rawHeader) {
            fclose($file);
            throw new \Exception('Le fichier est vide ou corrompu.');
        }

        // Nettoyer BOM et espaces
        $header = array_map(function($h) {
            $h = trim($h);
            $h = str_replace("\xEF\xBB\xBF", '', $h); // Supprime BOM UTF-8
            $h = preg_replace('/[\x00-\x1F\x7F]/u', '', $h); // Caractères de contrôle
            return $h;
        }, $rawHeader);

        \Log::info("Colonnes détectées: " . implode(' | ', $header));
        \Log::info("Nombre de colonnes: " . count($header));

        // ✅ Vérifier colonnes essentielles
        $requiredColumns = ['code_salle', 'code_jour', 'numero_seance', 'date_seance'];
        $missingColumns = array_diff($requiredColumns, $header);
        
        if (!empty($missingColumns)) {
            fclose($file);
            throw new \Exception('Colonnes manquantes: ' . implode(', ', $missingColumns));
        }

        $importedCount = 0;
        $skippedCount = 0;
        $batch = [];
        $csvLineNumber = 1;
        $errors = [];

        \DB::disableQueryLog();
        
        // ✅ DÉSACTIVER LES CONTRAINTES DE CLÉ ÉTRANGÈRE
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $csvLineNumber++;

            // Ignorer lignes vides
            if (empty(array_filter($row))) {
                continue;
            }

            if (count($header) !== count($row)) {
                $errors[] = "Ligne $csvLineNumber: " . count($header) . " colonnes attendues, " . count($row) . " trouvées";
                $skippedCount++;
                continue;
            }

            // Nettoyer les valeurs
            $row = array_map(function($v) {
                if (!is_string($v)) return $v;
                
                $v = trim($v);
                $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
                
                return $v === '' ? null : $v;
            }, $row);

            $data = array_combine($header, $row);

            // Debug première ligne de données
            if ($csvLineNumber === 2) {
                \Log::info("Première ligne de données: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            // Validation basique
            if (empty($data['code_salle']) || empty($data['date_seance'])) {
                $errors[] = "Ligne $csvLineNumber: code_salle ou date_seance vide";
                $skippedCount++;
                continue;
            }

            $batch[] = [
                'code_salle'        => $data['code_salle'],
                'code_jour'         => $data['code_jour'] ?? null,
                'numero_seance'     => $data['numero_seance'] ?? null,
                'date_seance'       => $data['date_seance'],
                'code_enseignant'   => $data['code_enseignant'] ?? null,
                'code_matiere'      => $data['code_matiere'] ?? null,
                'code_typeseance'   => $data['code_typeseance'] ?? 'CM',
                'code_groupe'       => $data['code_groupe'] ?? null,
                'code_effectue'     => 'P',
                'code_surveillance' => $data['code_surveillance'] ?? null,
                'locked_at'         => $data['locked_at'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Insertion par batch de 500
            if (count($batch) >= 500) {
                try {
                    \DB::table('seances')->insert($batch);
                    $importedCount += count($batch);
                } catch (\Exception $e) {
                    \Log::error("Erreur batch ligne ~$csvLineNumber: " . $e->getMessage());
                    $skippedCount += count($batch);
                }
                
                $batch = [];
                gc_collect_cycles();
            }
        }

        // Insérer le reste
        if (!empty($batch)) {
            try {
                \DB::table('seances')->insert($batch);
                $importedCount += count($batch);
            } catch (\Exception $e) {
                \Log::error("Erreur batch final: " . $e->getMessage());
                $skippedCount += count($batch);
            }
        }

        // ✅ RÉACTIVER LES CONTRAINTES
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        fclose($file);

        // Logger les premières erreurs
        if (!empty($errors)) {
            \Log::warning('Import CSV: lignes ignorées', [
                'total' => count($errors),
                'exemples' => array_slice($errors, 0, 10)
            ]);
        }

        $message = "Import terminé : $importedCount séances importées";
        if ($skippedCount > 0) {
            $message .= ", $skippedCount lignes ignorées";
        }

        return back()->with('success', $message);

    } catch (\Exception $e) {
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }

        // ✅ RÉACTIVER LES CONTRAINTES EN CAS D'ERREUR
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        \Log::error('Import CSV Séances échoué', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}