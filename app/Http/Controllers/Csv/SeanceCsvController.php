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
        'csv_file' => 'required|mimes:csv,txt|max:102400' // 100MB max
    ]);
     $path = $request->file('csv_file')->getRealPath();
    set_time_limit(0); // Pas de limite de temps
    ini_set('memory_limit', '1024M'); // 1GB
            $file = fopen($path, 'r');
        
        if (!$file) {
            throw new \Exception('Impossible d\'ouvrir le fichier.');
        }
    $path = $request->file('csv_file')->getRealPath();
      if (!stream_filter_append($file, 'convert.iconv.ISO-8859-15/UTF-8')) {
        // Essayer d'autres encodages courants
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
        
        // Désactiver les logs SQL pour la performance
        \DB::disableQueryLog();
        
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $csvLineNumber++;
            
            if (count($header) !== count($row)) {
                \Log::warning("Ligne $csvLineNumber ignorée: nombre de colonnes incorrect");
                continue;
            }
            
            $data = array_combine($header, $row);
            
            $batch[] = [
                'code_salle'        => $data['code_salle'] ?? '',
                'code_jour'         => $data['code_jour'] ?? '',
                'numero_seance'     => $data['numero_seance'] ?? '',
                'date_seance'       => $data['date_seance'] ?? '',
                'code_enseignant'   => $data['code_enseignant'] ?? '',
                'code_matiere'      => $data['code_matiere'] ?? null,
                'code_typeseance'   => $data['code_typeseance'] ?? 'CM', 
                'code_groupe'       => $data['code_groupe'] ?? '',
                'code_effectue'     => $data['code_effectue'] ?? '1',
                'code_surveillance' => $data['code_surveillance'] ?? null,
                'locked_at'         => $data['locked_at'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
            
            // Insert par lots de 1000
            if (count($batch) >= 1000) {
                \DB::table('seances')->insert($batch);
                $importedCount += count($batch);
                $batch = [];
                
                // Libérer la mémoire périodiquement
                if ($importedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        }
        
        // Dernier lot
        if (!empty($batch)) {
            \DB::table('seances')->insert($batch);
            $importedCount += count($batch);
        }
        
        fclose($file);
        
        return back()->with('success', "Import terminé : $importedCount séances importées.");
        
    } catch (\Exception $e) {
        // Fermeture sécurisée
        if (isset($file) && is_resource($file)) {
            fclose($file);
        }
        
        \Log::error('Import CSV échoué', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', "Échec de l'import : " . $e->getMessage());
    }
}
}
   
