<?php
/**
 * @var $this \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName string
 */

use Application\Entity\Db\These;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @var EntityManager $em */
$em = $controller->getServiceLocator()->get('doctrine.entitymanager.orm_default');

/** @var ClassMetadata[] $doctrineMetadatas */
$doctrineMetadatas = $em->getMetadataFactory()->getAllMetadata();
//$em->getMetadataFactory()->setCacheDriver(new FilesystemCache('/tmp/doctrine'));

$namespace = 'Application\Entity\Db';

$doctrineMetadatas = array_filter($doctrineMetadatas, function(ClassMetadata $meta) use ($namespace) {
    return $meta->namespace === $namespace;
});
$doctrineMetadatas = array_filter($doctrineMetadatas, function(ClassMetadata $meta) use ($namespace) {
    return !in_array(substr($meta->name, strlen($namespace) + 1), [
        'Privilege',
        'Role',
        'Utilisateur',
        'UniteRechercheIndividu',
        'WfEtape',
        'Parametre',
        'Variable',
        'ImportObserv',
        'ImportObservResult',
        'Civilite',
        'VWfEtapePertin',
        'Env',
        'VWorkflow',
    ]);
});
//var_dump($metas);

var_dump(preg_match("#1\s234,00#", "1 234,00"));

$metadatas = [];
foreach ($doctrineMetadatas as $meta) {
    $obj = new stdClass();
    $obj->name = $meta->name;
    $obj->columnNames = array_keys($meta->columnNames);
    $obj->associationMappings = array_keys($meta->associationMappings);
    $metadatas[$meta->name] = $obj;
//    var_dump($obj);
}

//var_dump($metadatas[These::class]);


// 'These.etatThese = E and These.codeUniteRecherche = E400 and These.dateSoutenance <= 30'

$qb = $em->getRepository(These::class)->createQueryBuilder('these')
    ->select('these, d, i')
    ->join('these.directeurs', 'd')
    ->join('d.individu', 'i')
    ->andWhere('these.codeUniteRecherche = \'EA4651\'')
    ;
//var_dump($qb->getQuery()->getArrayResult());

?>
<table class="table table-bordered">
    <tr>
        <th>name</th>
        <th>columnNames</th>
        <th>associationMappings</th>
    </tr>
    <?php foreach ($metadatas as $metadata): ?>
        <tr>
            <td><?php print_r($metadata->name) ?> </td>
            <td><?php print_r(implode('<br>', $metadata->columnNames )) ?> </td>
            <td><?php print_r(implode('<br>', $metadata->associationMappings)) ?> </td>
        </tr>
    <?php endforeach ?>
</table>

