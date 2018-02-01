<?php

namespace Application\Service\Etablissement;

use Application\Entity\Db\Repository\EtablissementRepository;
use Application\Entity\Db\Etablissement;
use Application\Service\BaseService;

/**
 * @method Etablissement|null findOneBy(array $criteria, array $orderBy = null)
 */
class EtablissementService extends BaseService
{
    /**
     * @return EtablissementRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Etablissement::class);
    }
}