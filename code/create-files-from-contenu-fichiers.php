<?php
/**
 * @var $this \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName string
 */

use Application\Entity\Db\ContenuFichier;
use Application\Service\Fichier\FichierService;

//////////////////////////////////////////////////////////////////////////
//
// Parcours en BDD des fichiers déposés pour les stocker sur le disque.
//
//////////////////////////////////////////////////////////////////////////

set_time_limit(120);

/** @var FichierService $fs */
$fs = $controller->getServiceLocator()->get('FichierService');
$rootdir = $fs->getRootDirectoryPath();
//$rootdir = '/tmp/sygal-files';

$repo = $fs->getEntityManager()->getRepository(ContenuFichier::class);
$qb = $repo->createQueryBuilder('cf')
    ->addSelect('f, nf')
    ->join('cf.fichier', 'f')
    ->join('f.nature', 'nf')
    ->where('1 = pasHistorise(f)');
/** @var ContenuFichier[] $result */
$result = $qb/*->setMaxResults(50)*/->getQuery()->getArrayResult();

$createdFiles = [];
$existingFiles = [];
$totalSize = 0;

foreach ($result as $cf) {
    $dir = $rootdir . '/' . strtolower($cf['fichier']['nature']['code']);

    $filename = $cf['fichier']['nom'];
    $filesize = $cf['fichier']['taille'];
    $filepath = $dir . '/' . $filename;

    if (file_exists($filepath)) {
        $existingFiles[] = $filepath;
        $totalSize += filesize($filepath);
        continue;
    }

    if (! \Application\Util::createWritableFolder($dir, 0770)) {
        throw new RuntimeException("Le répertoire suivant n'a pas pu être créé sur le serveur : " . $dir);
    }

    $content = stream_get_contents($cf['data']);
    file_put_contents($filepath, $content);

    if (! file_exists($filepath)) {
        throw new \UnicaenApp\Exception\RuntimeException("Created file not found : " . $filepath);
    }
//    if (($size = filesize($filepath)) !== $filesize) {
//        throw new \UnicaenApp\Exception\RuntimeException("Size mismatch ($size !== $filesize) for " . $filepath);
//    }

    $createdFiles[] = $filepath;
    $totalSize += filesize($filepath);
}

echo 'Fichiers déjà présents :';
var_dump($existingFiles);
echo 'Fichiers créés :';
var_dump($createdFiles);
echo 'Taille totale :';
var_dump($totalSize . ' (' . $totalSize/1024/1024 . ' Mo)');
