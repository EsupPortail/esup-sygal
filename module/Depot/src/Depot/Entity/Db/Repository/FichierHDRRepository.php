<?php

namespace Depot\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Utilisateur;
use Depot\Entity\Db\FichierHDR;
use Depot\QueryBuilder\FichierHDRQueryBuilder;
use Doctrine\ORM\Query\Expr;
use Fichier\Entity\Db\NatureFichier;
use Fichier\Entity\Db\VersionFichier;
use HDR\Entity\Db\HDR;

/**
 * @method FichierHDRQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class FichierHDRRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected string $queryBuilderClassName = FichierHDRQueryBuilder::class;

    /**
     * Retourne les fichiers liés à une HDR, qui ont la nature et version spécifiées.
     *
     * @param HDR $hdr
     * @param NatureFichier|string $nature
     * @param VersionFichier|string $version
     * @param Utilisateur $auteur
     * @return FichierHDR[]
     */
    public function fetchFichierHDR(HDR $hdr, $nature = null, $version = null, $auteur = null)
    {
        $qb = $this->createQueryBuilder("fhdr");

        $qb->join("fhdr.fichier", "f");

        if ($nature !== null) {
            if (!$nature instanceof NatureFichier) {
                $qb->join("f.nature", "n", Expr\Join::WITH, "n.code=:nature");
            } else {
                $qb->andWhere("f.nature = :nature");
            }
            $qb->setParameter("nature", $nature);
        }

        if ($version !== null) {
            if (!$version instanceof VersionFichier) {
                $qb->join("f.version", "v", Expr\Join::WITH, "v.code = :version");
            } else {
                $qb->andWhere("f.version = :version");
            }
            $qb->setParameter("version", $version);
        }

        if ($auteur !== null) {
            $qb->andWhere('f.histoModificateur = :auteur')
                ->setParameter('auteur', $auteur);
        }

        $qb->andWhere("fhdr.hdr = :hdr");
        $qb->setParameter("hdr", $hdr);

        $qb->andWhere("f.histoDestruction is null");

        $qb->addOrderBy('f.histoModification', 'ASC');

        return $qb->getQuery()->getResult();
    }
    public function hasVersion(HDR $hdr, $version)
    {
        $fichiers = $this->fetchFichierHDR($hdr, NatureFichier::CODE_HDR_PDF, $version);
        return !empty($fichiers);
    }
}