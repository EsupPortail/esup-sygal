<?php

namespace Application\Service\TitreAcces;

use Application\Entity\Db\Pays;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\TitreAcces;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;

class TitreAccesService extends BaseService
{
    use SourceServiceAwareTrait;

    /**
     * @return DefaultEntityRepository
     */
    public function getRepository(): DefaultEntityRepository
    {
        /** @var DefaultEntityRepository $repo */
        $repo = $this->entityManager->getRepository(TitreAcces::class);

        return $repo;
    }

    /**
     * @return TitreAcces
     */
    public function newTitreAcces(): TitreAcces
    {
        $titreAcces = new TitreAcces();
        $titreAcces->setSource($this->sourceService->fetchApplicationSource());
        $titreAcces->setSourceCode($this->sourceService->genereateSourceCode());

        return $titreAcces;
    }
}