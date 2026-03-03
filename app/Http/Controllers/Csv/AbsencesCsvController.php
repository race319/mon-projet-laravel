<?php

namespace App\Http\Controllers\Csv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absence;


class AbsencesCsvController extends Controller
{

public function download()
{
    $absences = \DB::table('absence')->get();

    $filename = 'absences.csv';
    $handle = fopen($filename, 'w+');

    
    fputcsv($handle, [
        'code_etudiant',
        'code_groupe',
        'code_matiere',
        'code_enseignant',
        'date_absence',
        'seance',
        'statut',
        'justifie',
        'elimination',
        'created_at',
        'updated_at'
    ], ';');

    foreach ($absences as $a) {

        // Conversion de l’élimination
        $elimination = ($a->elimination == 1) ? 'elimine' : 'Non';

        fputcsv($handle, [
            $a->code_etudiant,
            $a->code_groupe,
            $a->code_matiere,
            $a->code_enseignant,
            $a->date_absence,
            $a->seance,
            $a->statut,
            $a->justifie,
            $elimination, // <-- ici la correction
            $a->created_at,
            $a->updated_at
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
                    'code_etudiant'    => $data['code_etudiant'] ?? '',
                    'code_groupe'      => $data['code_groupe'] ?? '',
                    'code_matiere'     => $data['code_matiere'] ?? '',
                    'code_enseignant'  => $data['code_enseignant'] ?? '',
                    'seance'           => $data['seance'] ?? 1,
                    'statut'           => $data['statut'] ?? 'Absent',
                    'justifie'         => $data['justifie'] ?? false,
                    'elimination'      => $data['elimination'] ?? false,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                if (count($batch) >= 1000) {
                    \DB::table('absence')->insert($batch);
                    $importedCount += count($batch);
                    $batch = [];

                    if ($importedCount % 10000 === 0) {
                        gc_collect_cycles();
                    }
                }
            }

            if (!empty($batch)) {
                \DB::table('absence')->insert($batch);
                $importedCount += count($batch);
            }

            fclose($file);

            return back()->with('success', "Import terminé : $importedCount absences importées.");

        } catch (\Exception $e) {
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }

            \Log::error('Import CSV Absences échoué', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', "Échec de l'import : " . $e->getMessage());
        }
    }
}