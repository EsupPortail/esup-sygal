<?php

namespace Admission\Hydrator\Verification;

use Admission\Service\Document\DocumentService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Inscription\InscriptionService;
use Application\Application\Form\Hydrator\RecrutementHydrator;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class VerificationHydratorFactory implements FactoryInterface
{
    /**
     * Create hydrator
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return VerificationHydrator
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        return new VerificationHydrator($entityManager);

//        $etudiantService = $container->get(EtudiantService::class);
//        $inscriptionService = $container->get(InscriptionService::class);
//        $financementService = $container->get(FinancementService::class);
//        $documentService = $container->get(DocumentService::class);
//        $hydrator = new VerificationHydrator();
//        $hydrator->setEtudiantService($etudiantService);
//        $hydrator->setInscriptionService($inscriptionService);
//        $hydrator->setFinancementService($financementService);
//        $hydrator->setDocumentService($documentService);


//        return $hydrator;
    }
}