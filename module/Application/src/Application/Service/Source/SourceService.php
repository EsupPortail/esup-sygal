<?php

namespace Application\Service\Source;

use Application\Entity\Db\Source;
use Application\Service\BaseService;
use Doctrine\ORM\EntityRepository;
use UnicaenApp\Exception\RuntimeException;

/**
 * @author Unicaen
 */
class SourceService extends BaseService
{
    const APPL_SOURCE_CODE = 'SYGAL::sygal';

    const SQL_TEMPLATE_CREATE_APP_SOURCE =
        "INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, '%s', 'SyGAL', 0);";

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
    public function fetchApplicationSource()
    {
        $codeApp = self::APPL_SOURCE_CODE;

        /** @var Source $source */
        $source = $this->getRepository()->findOneBy(['code' => $codeApp]);

        if ($source === null) {
            throw new RuntimeException(
                "Anomalie: la source '$codeApp' doit exister dans la base de données. " . PHP_EOL .
                "Vous pouvez la créer ainsi : " . PHP_EOL .
                sprintf(self::SQL_TEMPLATE_CREATE_APP_SOURCE, $codeApp)
            );
        }

        return $source;
    }
}