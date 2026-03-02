<?php

// Nom du fichier CSV à générer
$filename = "groupes_etudiants.csv";

// Liste complète de tous les groupes
$groupes = [
    'CO13','CO13 CR 1','CO13 CR 2','CO13 Ex 1','CO13 Ex 2','CO13 Ex 3','CO13 TD 1','CO13 TD 2','CO13 TD 3','CO13 TD 4',
    'CO15','CO15 CR 1','CO15 CR 2','CO15 Ex 1','CO15 Ex 2','CO15 Ex 3','CO15 Ex 4','CO15 TD 1','CO15 TD 2','CO15 TD 3','CO15 TD 4',
    'FD53','FD53 Ex 1','LA15','LA15 Ex 1','LB15','LB15 Ex 1','LC15','LC15 Ex 1','LC15 Ex 2','LC15 Ex 3','LC15 TD 1','LC15 TD 2',
    'LE11','LE11 CR 1','LE11 CR 2','LE11 CR 3','LE11 CR 4','LE11 CR 5','LE11 CR 6','LE11 CR 7','LE11 CR 8','LE11 CR 9',
    'LE11 Ex 1','LE11 Ex 2','LE11 Ex 3','LE11 Ex 4','LE11 Ex 5','LE11 Ex 6','LE11 Ex 7','LE11 Ex 8','LE11 Ex 9','LE11 Ex 10',
    'LE11 Ex 11','LE11 Ex 12','LE11 Ex 13','LE11 Ex 14','LE11 Ex 15','LE11 Ex 16','LE11 Ex 17','LE11 Ex 18','LE11 Ex 19','LE11 Ex 20',
    'LE11 Ex 21','LE11 Ex 22','LE11 Ex 23','LE11 Ex 24','LE11 Ex 25','LE11 Ex 26','LE11 Ex 27','LE11 Ex 28','LE11 Ex 29','LE11 Ex 30','LE11 Ex 31',
    'LE11 TD 1','LE11 TD 2','LE11 TD 3','LE11 TD 4','LE11 TD 5','LE11 TD 6','LE11 TD 7','LE11 TD 8','LE11 TD 9','LE11 TD 10','LE11 TD 11','LE11 TD 12','LE11 TD 13','LE11 TD 14','LE11 TD 15','LE11 TD 16','LE11 TD 17','LE11 TD 18','LE11 TD 19','LE11 TD 20','LE11 TD 21','LE11 TD 22','LE11 TD 23','LE11 TD 24','LE11 TD 25','LE11 TD 26','LE11 TD 27',
    // ... continue avec tous les autres groupes
    'RV51','RV51 Ex 1','RV53','RV53 Ex 1'
];

// Fonction pour générer un nom d'étudiant aléatoire
function generateStudentName() {
    $firstNames = ['Ahmed','Sara','Mohamed','Aya','Omar','Lina','Youssef','Mouna','Ali','Nour','Hassan','Salma','Karim','Maya','Amine'];
    $lastNames = ['Ben Amor','Trabelsi','Haddad','Kacem','Benzarti','Saidi','Fakhfakh','Jaziri','Gharbi','Ayari','Mabrouk','Mansour','Hafsi','Mahjoub','Khaldi'];
    $firstName = $firstNames[array_rand($firstNames)];
    $lastName = str_replace(',', '', $lastNames[array_rand($lastNames)]); // enlever les virgules
    return $firstName . ' ' . $lastName;
}

// Création du fichier CSV
$fp = fopen($filename, 'w');

// En-têtes CSV avec point-virgule comme séparateur
fputcsv($fp, ['code_etudiant','code_groupe'], ';');

// Générer 5 étudiants par groupe
foreach ($groupes as $groupe) {
    for ($i = 0; $i < 5; $i++) {
        $studentName = generateStudentName();
        fputcsv($fp, [$studentName, $groupe], ';'); // utiliser ; comme séparateur
    }
}

fclose($fp);

echo "Le fichier CSV '$filename' a été généré avec succès.\n";
?>