<?php

namespace Application\Service\Source;

use Application\Entity\Db\Source;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\EntityRepository;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

/**
 * @author Unicaen
 */
class SourceService extends BaseService
{
    use SourceCodeStringHelperAwareTrait;

    const APPL_SOURCE_CODE = 'SYGAL::sygal';

    const SQL_TEMPLATE_CREATE_APP_SOURCE =
        "INSERT INTO SOURCE (ID, CODE, LIBELLE, IMPORTABLE) VALUES (1, '%s', 'ESUP-SyGAL', 0);";

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        /** @var EntityRepository $repo */
        $repo = $this->entityManager->getRepository(Source::class);

        return $repo;
    }

    /**
     * @return Source
     */
    public function fetchApplicationSource(): Source
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

    /**
     * Génère un SOURCE_CODE par défaut.
     *
     * @return string
     */
    public function genereateSourceCode(): string
    {
        return $this->sourceCodeStringHelper->addDefaultPrefixTo(uniqid());
    }
}