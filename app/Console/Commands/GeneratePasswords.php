<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GeneratePasswords extends Command
{
    protected $signature = 'passwords:generate';
    protected $description = 'Générer 500 mots de passe de 6 caractères compatibles Laravel';

    public function handle()
    {
        // chemin du fichier
        $path = storage_path('app/passwords.csv');

        // créer le dossier storage/app si nécessaire
        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // supprimer l'ancien fichier si il existe
        if (file_exists($path)) {
            unlink($path);
        }

        // ouvrir le fichier
        $file = fopen($path, 'w');

        // écrire l'entête avec ; comme séparateur
        fputcsv($file, ['plain_password', 'hashed_password'], ';');

        // générer 500 mots de passe
        for ($i = 1; $i <= 500; $i++) {
            $plain = Str::random(6);
            $hashed = Hash::make($plain);
            fputcsv($file, [$plain, $hashed], ';');
        }

        fclose($file);

        $this->info("✅ 500 mots de passe générés dans storage/app/passwords.csv");
    }
}