<?php

namespace Application\Service\Source;

use Application\Entity\Db\SourceInterface;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;
use UnicaenImport\Entity\Db\Source;

/**
 * @author Unicaen
 */
class SourceService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Source::class);

        return $repo;
    }

    /**
     * @return Source
     */
    public function fetchSourceSygal()
    {
        /** @var Source $source */
        $source = $this->getRepository()->findOneBy(['code' => SourceInterface::CODE_SYGAL]);

        return $source;
    }
}