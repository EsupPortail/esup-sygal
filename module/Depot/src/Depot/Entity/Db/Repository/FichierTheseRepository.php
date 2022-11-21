<?php

namespace Depot\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Depot\Entity\Db\FichierThese;
use Fichier\Entity\Db\NatureFichier;
use These\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\ValiditeFichier;
use Fichier\Entity\Db\VersionFichier;
use Depot\QueryBuilder\FichierTheseQueryBuilder;
use Doctrine\ORM\Query\Expr;

/**
 * @method FichierTheseQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class FichierTheseRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected string $queryBuilderClassName = FichierTheseQueryBuilder::class;

    /**
     * Retourne les fichiers liés à une thèse, qui ont la nature et version spécifiées.
     *
     * @param These $these
     * @param NatureFichier|string $nature
     * @param VersionFichier|string $version
     * @param int|bool|string $retraitement '0', '1', booléen ou code du retraitementOTH
     * @param Utilisateur $auteur
     * @return FichierThese[]
     */
    public function fetchFichierTheses(These $these, $nature = null, $version = null, $retraitement = null, $auteur = null)
    {
        $qb = $this->createQueryBuilder("ft");

        $qb->join("ft.fichier", "f");

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

        if ($retraitement !== null) {

            if (is_numeric($retraitement) || is_bool($retraitement)) {
                $retraitement = (bool) $retraitement;
                if ($retraitement) {
                    $qb->andWhere("ft.retraitement IS NOT NULL");
                } else {
                    $qb->andWhere("ft.retraitement IS NULL");
                }
            } else {
                $qb->andWhere("ft.retraitement = :estRetraite");
                $qb->setParameter("estRetraite", $retraitement);
            }
        }

        if ($auteur !== null) {
            $qb->andWhere('f.histoModificateur = :auteur')
                ->setParameter('auteur', $auteur);
        }

        $qb->andWhere("ft.these = :these");
        $qb->setParameter("these", $these);

        $qb->andWhere("f.histoDestruction is null");

        $qb->addOrderBy('f.histoModification', 'ASC');

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
     * @return FichierThese|null
     */
    public function getVersionArchivable(These $these)
    {
        $theseFichiers = $this->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ORIG, false);
        /** @var FichierThese $fichierThese */
        $fichierThese = current($theseFichiers);
        /** @var ValiditeFichier $validiteFichierThese */
        $validiteFichierThese = $fichierThese ? $fichierThese->getFichier()->getValidite() : null;

        if ($validiteFichierThese && $validiteFichierThese->getEstValide() === true) {
            return $fichierThese;
        }

        $theseFichiersRetraites = $this->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , VersionFichier::CODE_ARCHI, true);
        /** @var FichierThese $fichierTheseRetraite */
        $fichierTheseRetraite = current($theseFichiersRetraites);
        /** @var ValiditeFichier $validiteFichierTheseRetraite */
        $validiteFichierTheseRetraite = $fichierTheseRetraite ? $fichierTheseRetraite->getFichier()->getValidite() : null;

        if ($validiteFichierTheseRetraite && $validiteFichierTheseRetraite->getEstValide() === true
            && $fichierTheseRetraite->getEstConforme()) {
            return $fichierTheseRetraite;
        }

        return null;
    }

    public function hasVersion(These $these, $version)
    {
        $fichiers = $this->fetchFichierTheses($these, NatureFichier::CODE_THESE_PDF , $version);
        return !empty($fichiers);
    }
}