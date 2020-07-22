<?php

namespace Application\Service\NatureFichier;

use Application\Entity\Db\NatureFichier;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;

class NatureFichierService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(NatureFichier::class);
    }

    /**
     * @param $code
     * @return null|NatureFichier
     */
    public function fetchNatureFichierByCode($code)
    {
        /** @var NatureFichier $nature */
        $nature = $this->getRepository()->findOneBy(['code' => $code]);

        return $nature;
    }
}