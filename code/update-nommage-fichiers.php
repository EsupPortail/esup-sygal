<?php
/**
 * @var $this \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName string
 */

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Application\Filter\NomFichierFormatter;
use Application\Service\Fichier\FichierService;
use Doctrine\ORM\EntityManager;

set_time_limit(120);

/** @var FichierService $fs */
$fs = $controller->getServiceLocator()->get('FichierService');
//$rootdir = $fs->getRootDirectoryPath();
$rootdir = '/tmp/sygal-files';

$repo = $fs->getEntityManager()->getRepository(Fichier::class);
$qb = $repo->createQueryBuilder('f');
$qb
    ->addSelect('nf, t')
    ->join('f.nature', 'nf')
    ->join('f.these', 't')
    ->where('1 = pasHistorise(f)')
//    ->andWhere($qb->expr()->in('nf.code', [NatureFichier::CODE_THESE_PDF, NatureFichier::CODE_FICHIER_NON_PDF]))
    ->orderBy('nf.code');
/** @var Fichier[] $fichiers */
$fichiers = $qb/*->setMaxResults(50)*/->getQuery()->getResult();

$nomFichierFormatter = new NomFichierFormatter();

foreach ($fichiers as $fichier) {
    $newNom = $nomFichierFormatter->filter($fichier);
    $fichier->setNom($newNom);
}

/** @var EntityManager $em */
$em = $controller->getServiceLocator()->get('doctrine.entitymanager.orm_default');
$em->flush($fichiers);
?>

<table>
    <thead>
        <tr>
            <th>Nature</th>
            <th>Nom</th>
            <th>Nom original</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($fichiers as $fichier): ?>
        <tr>
            <td><?php echo $fichier->getNature()->getCode() ?></td>
            <td><?php echo $fichier->getNom() ?></td>
            <td><?php echo $fichier->getNomOriginal() ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>