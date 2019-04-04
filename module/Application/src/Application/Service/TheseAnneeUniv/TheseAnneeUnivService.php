<?php

namespace Application\Service\TheseAnneeUniv;

use Application\Entity\Db\Repository\TheseAnneeUnivRepository;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Service\BaseService;

class TheseAnneeUnivService extends BaseService
{
    public function getRepository()
    {
        /** @var TheseAnneeUnivRepository $repo */
        $repo = $this->entityManager->getRepository(TheseAnneeUniv::class);

        return $repo;
    }

}