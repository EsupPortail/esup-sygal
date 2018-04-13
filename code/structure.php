<?php

/**
 * @var $this \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName string
 */

use Application\Entity\Db\Structure;
use Application\Entity\Db\UniteRecherche;
use Application\Service\Structure\StructureService;


/** @var StructureService $structureService */
$structureService = $controller->getServiceLocator()->get(StructureService::class);

///** @var Structure $s1 */
///** @var Structure $s2 */
///** @var Structure $s3 */
//$s1 = $structureService->getEntityManager()->getRepository(UniteRecherche::class)->findOneBySourceCode('UCN::UMR6176');
//$s2 = $structureService->getEntityManager()->getRepository(UniteRecherche::class)->findOneBySourceCode('UCN::UMR6185');
//$s3 = $structureService->getEntityManager()->getRepository(UniteRecherche::class)->findOneBySourceCode('UCN::UMR6194');
//
//$dataObject = new UniteRecherche();
//$dataObject->setLibelle('UMR6176 * UMR6185 * UMR6194');
//$dataObject->setCheminLogo('/tmp/test.png');
//$dataObject->setSigle('U*U*U');
//
//$strSubstits = $structureService->createStructureSubstitutions([$s1, $s2, $s3], $dataObject);
//$sc = $strSubstits[0]->getToStructure();


/** @var UniteRecherche $sc */
$sc = $structureService->getEntityManager()->getRepository(UniteRecherche::class)->createQueryBuilder('ur')
    ->join('ur.structure', 's')
    ->where('s.sigle = :sigle')
    ->setParameter('sigle', 'U*U*U')
    ->getQuery()->getSingleResult();
//var_dump($sc, count($sc->getStructure()->getStructuresSubstituees()));
array_map(
    function(Structure $s) {
        var_dump($s->getSourceCode());
    },
    $sc->getStructure()->getStructuresSubstituees()->toArray()
);




//$sc = $structureService->getEntityManager()->getRepository(UniteRecherche::class)->findOneBySigle('U*U*U');
