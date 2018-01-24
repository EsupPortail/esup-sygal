<?php

namespace Application\Service\Env;

use Application\Entity\Db\Env;
use Application\Entity\Db\Repository\EnvRepository;
use Application\Service\BaseService;

/**
 * @method Env|null findOneBy(array $criteria, array $orderBy = null)
 */
class EnvService extends BaseService
{
    /**
     * @return EnvRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Env::class);
    }

    /**
     * Recherche les données d'environnement pour l'année fournie.
     *
     * Deux cas de figure :
     *
     * 1) Une année est fournie :
     * On fait la recherche pour cette année.
     * Si la recherche ne donne rien et que c'est demandé, on refait la recherche avec année = NULL.
     *
     * 2) Aucune année n'est fournie :
     * On fait la recherche avec année = NULL.
     *
     * @param int  $annee
     * @param bool $retryWithNull Si la recherche est infructueuse pour l'année spécifiée,
     *                            faut-il refaire la recherche avec annee = NULL ?
     * @return Env|null
     */
    public function findOneByAnnee($annee = null, $retryWithNull = true)
    {
        if ($annee) {
            /** @var Env $env */
            $env = $this->getRepository()->findOneBy(['annee' => $annee]);
            if ($env) {
                return $env;
            }
        }

        if (! $retryWithNull) {
            return null;
        }

        $qb = $this->getRepository()->createQueryBuilder('e')
            ->where('e.annee is null');

        $env = $qb->getQuery()->getOneOrNullResult();

        return $env;
    }
}