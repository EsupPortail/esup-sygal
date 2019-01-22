<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\ValiditeFichier;
use Application\Entity\Db\VersionFichier;
use Application\Entity\Db\NatureFichier;
use Application\QueryBuilder\FichierQueryBuilder;
use Doctrine\ORM\Query\Expr;


/**
 * @method FichierQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class FichierRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = FichierQueryBuilder::class;

    /**
     * Retourne les fichiers liés à une thèse, qui ont la nature et version spécifiées.
     *
     * @param These $these
     * @param NatureFichier|string $nature
     * @param VersionFichier|string $version
     * @param int|bool|string $retraitement '0', '1', booléen ou code du retraitementOTH
     * @return Fichier[]
     */
    public function fetchFichiers(These $these, $nature = null, $version = null, $retraitement = null)
    {
        $qb = $this->createQueryBuilder("f");

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
                $qb->join("f.version", "v", Expr\Join::WITH, "v.code=:version");
            } else {
                $qb->andWhere("f.version = :version");
            }
            $qb->setParameter("version", $version);
        }

        if ($retraitement !== null) {

            if (is_numeric($retraitement) || is_bool($retraitement)) {
                $retraitement = (bool) $retraitement;
                if ($retraitement) {
                    $qb->andWhere("f.retraitement IS NOT NULL");
                } else {
                    $qb->andWhere("f.retraitement IS NULL");
                }
            } else {
                $qb->andWhere("f.retraitement = :estRetraite");
                $qb->setParameter("estRetraite", $retraitement);
            }
        }

        $qb->andWhere("f.these = :these");
        $qb->setParameter("these", $these);

        $qb->andWhere("1 = pasHistorise(f)");

        return $qb->getQuery()->getResult();
    }

    public function existeVersionArchivable(These $these)
    {
        return (bool) $this->getVersionArchivable($these);
    }

    /**
     * Retourne la version archivable de la thèse, s'il elle existe.
     *
     * C'est soit la version originale si elle est archivable.
     * Soit la version retraitée si elle est archivable et vérifiée conforme.
     *
     * @param These $these
     * @return Fichier|null
     */
    public function getVersionArchivable(These $these)
    {
//        $theseFichiers = $this->getFichiersBy(false, false, false);
        $theseFichiers = $this->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG, false);
        /** @var Fichier $fichierThese */
        $fichierThese = current($theseFichiers);
        /** @var ValiditeFichier $validiteFichierThese */
        $validiteFichierThese = $fichierThese ? $fichierThese->getValidite() : null;

        if ($validiteFichierThese && $validiteFichierThese->getEstValide() === true) {
            return $fichierThese;
        }

//        $theseFichiersRetraites = $this->getFichiersBy(false, false, true);
        $theseFichiersRetraites = $this->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI, true);
        /** @var Fichier $fichierTheseRetraite */
        $fichierTheseRetraite = current($theseFichiersRetraites);
        /** @var ValiditeFichier $validiteFichierTheseRetraite */
        $validiteFichierTheseRetraite = $fichierTheseRetraite ? $fichierTheseRetraite->getValidite() : null;

        if ($validiteFichierTheseRetraite && $validiteFichierTheseRetraite->getEstValide() === true
            && $fichierTheseRetraite->getEstConforme()) {
            return $fichierTheseRetraite;
        }

        return null;
    }


    public function hasVersion(These $these,  $version)
    {
        $fichiers = $this->fetchFichiers($these, NatureFichier::CODE_THESE_PDF , $version);
        return !empty($fichiers);
    }

    /**
     * @param NatureFichier|string $nature
     * @return Fichier[]
     */
    public function fetchFichiersByNature($nature)
    {
        $qb = $this->createQueryBuilder("fichier")
            ->andWhere('fichier.nature = :nature')
            ->andWhere('fichier.histoDestruction IS NULL')
            ->setParameter("nature", $nature)
            ->orderBy('fichier.histoCreation')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}