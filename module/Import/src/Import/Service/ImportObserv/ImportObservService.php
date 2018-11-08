<?php

namespace Import\Service\ImportObserv;

use Application\Entity\Db\ImportObserv;
use UnicaenImport\Service\AbstractService;

/**
 * @author Unicaen
 */
class ImportObservService extends AbstractService
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getEntityManager()->getRepository(ImportObserv::class);
    }
}