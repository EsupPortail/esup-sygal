<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\VersionFichier;
use Doctrine\ORM\QueryBuilder;

/**
 * @method VersionFichier findOneByCode($code)
 */
class VersionFichierRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = QueryBuilder::class;

    /**
     * @param bool $corrige
     * @return VersionFichier
     */
    public function fetchVersionOriginale($corrige = false)
    {
        $code = $corrige ? VersionFichier::CODE_ORIG_CORR : VersionFichier::CODE_ORIG;
        /** @var VersionFichier $vf */
        $vf = $this->findOneBy(['code' => $code]);

        return $vf;
    }

    /**
     * @param bool $corrige
     * @return VersionFichier
     */
    public function fetchVersionArchivage($corrige = false)
    {
        $code = $corrige ? VersionFichier::CODE_ARCHI_CORR : VersionFichier::CODE_ARCHI;
        /** @var VersionFichier $vf */
        $vf = $this->findOneBy(['code' => $code]);

        return $vf;
    }

    /**
     * @param bool $corrige
     * @return VersionFichier
     */
    public function fetchVersionDiffusion($corrige = false)
    {
        $code = $corrige ? VersionFichier::CODE_DIFF_CORR : VersionFichier::CODE_DIFF;
        /** @var VersionFichier $vf */
        $vf = $this->findOneBy(['code' => $code]);

        return $vf;
    }
}