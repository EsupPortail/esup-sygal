<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$n = $_SERVER['N'] ?? 1;

$sourceCode = $_SERVER[$k = 'SOURCE_CODE'] ?? null;
if ($sourceCode === null) {
    echo "Le code de la SOURCE (correspondant à l'Instance Pégase) doit être spécifié dans la variable d'env '$k'." . PHP_EOL;
    exit(1);
}

$outputDirPath = '/tmp/api-data';
if (!file_exists($outputDirPath)) {
    echo "Répertoire de sortie introuvable : $outputDirPath" . PHP_EOL;
    exit(1);
}

$twigTemplateDirPath = realpath(__DIR__);
$twigTemplateName = 'doctorant-api-v1.data.twig.json'; // nom du template dans le répertoire des templates Twig

echo "Génération de $n fichiers JSON à partir du modèle $twigTemplateDirPath/$twigTemplateName..." . PHP_EOL;

$args = implode(' ', array_slice($_SERVER['argv'], 1));

$loader = new \Twig\Loader\FilesystemLoader($twigTemplateDirPath);
$twig = new \Twig\Environment($loader);

$f = Faker\Factory::create('fr_FR');
$jsonGen = fn($ine) => $twig->render($twigTemplateName, [
    'instance_pegase' => $sourceCode,
    'apprenant_code' => $f->randomNumber(7, true),
    'apprenant_genre' => $f->randomElement(['M', 'F']),
    'apprenant_INE' => $ine,
    'apprenant_deuxiemePrenom' => $f->firstName(),
    'apprenant_mailPersonnel' => $f->email(),
    'apprenant_mailUrgence' => $f->email(),
    'apprenant_nomDeNaissance' => $nom = $f->lastName(),
    'apprenant_nomUsuel' => $nom,
    'apprenant_prenom' => $prenom = $f->firstName(),
    'apprenant_prenomUsage' => $prenom,
    'inscription_ecoleDoctorale' => $f->randomElement(['98', '591', '590', '558', '497']),
    'inscription_chemin' => 'LICENCE_MAI>LICENCE-MAI-L1',
    'inscription_codeStructureEtablissementDuChemin' => 'ETAB' . $f->randomNumber(2),
    'inscription_periode_code' => 'PER-' . $f->randomElement(['2019', '2020', '2021', '2022', '2023']),
]);

for ($i=1; $i<=$n; ++$i) {
    $ine = $f->randomNumber(9, true);
    file_put_contents($filepath = $outputDirPath . "/inscr-admin-$sourceCode-$ine.json", $jsonGen($ine));
    chmod($filepath, 0666);
    echo $filepath . PHP_EOL;
}
