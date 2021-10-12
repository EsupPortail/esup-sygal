<?php

use Application\Validator\FichierCinesValidator;
use Retraitement\RetraitValid;
use Laminas\Console;

$appRoot = realpath(__DIR__ . '/..');

$loader = include $appRoot . '/vendor/autoload.php';

$classmapFile = $appRoot . '/autoload_classmap.php';
if (!file_exists($classmapFile)) {
    fwrite(STDERR,
        "Vous devez au préalable générer le fichier '$classmapFile' à l'aide de la commande suivante :" . PHP_EOL .
        "  php $appRoot/vendor/bin/classmap_generator.php -l $appRoot/module -o $appRoot/autoload_classmap.php" . PHP_EOL);
    die(1);
}
$loader->addClassMap(require $classmapFile);


$rules = array(
    'help|h'     => 'Aide.',
    'input|i=s'  => 'Obligatoire. Chemin vers le répertoire à parcourir, ou vers le fichier CSV à lire.',
    'output|o=s' => 'Obligatoire. Chemin vers le répertoire où seront créés les fichiers retraités et le fichiers CSV de log.',
);

try {
    $opts = new Console\Getopt($rules);
    $opts->parse();
} catch (Console\Exception\RuntimeException $e) {
    echo $e->getUsageMessage();
    exit(2);
}

if ($opts->getOption('h')) {
    echo $opts->getUsageMessage();
    exit(0);
}

$inputFilePath = $opts->getOption('i');
$outputDir = $opts->getOption('o');

if (! $inputFilePath || ! $outputDir) {
    echo $opts->getUsageMessage();
    exit(2);
}

if (! file_exists(realpath($inputFilePath))) {
    echo "$inputFilePath n'existe pas";
    exit(1);
}
if (! file_exists(realpath($outputDir))) {
    echo "$outputDir n'existe pas";
    exit(1);
}


$validator = new FichierCinesValidator(['script_path' => __DIR__ . '/validation_cines.sh']);

$rv = new RetraitValid($validator);
$rv->execute(
    realpath($inputFilePath),
    realpath($outputDir)
);