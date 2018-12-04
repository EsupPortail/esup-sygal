<?php

namespace Application\Service\Source;

use Application\Entity\Db\Source;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;

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
        $source = $this->getRepository()->findOneBy(['code' => Source::CODE_SYGAL]);

        return $source;
    }
}