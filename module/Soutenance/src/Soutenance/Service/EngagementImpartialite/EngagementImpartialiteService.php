<?php

namespace Soutenance\Service\EngagementImpartialite;

use Application\Entity\Db\Acteur;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Soutenance\Entity\Membre;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class EngagementImpartialiteService {
    use ValidatationServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Membre $membre
     * @param These $these
     * @return Validation
     */
    public function create(Membre $membre, These $these)
    {
        $validation = $this->getValidationService()->create(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these, $membre->getIndividu());
        return $validation;
    }

    /**
     * @param Membre $membre
     * @param These $these
     * @return Validation
     */
    public function createRefus(Membre $membre, These $these)
    {
        $validation = $this->getValidationService()->create(TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE, $these, $membre->getIndividu());
        return $validation;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function delete(Membre $membre)
    {
        $these = $membre->getProposition()->getThese();
        $validation = $this->getEngagementImpartialiteByMembre($these, $membre);
        $validation = $this->getValidationService()->historiser($validation);
        return $validation;
    }

    /** REQUETE *******************************************************************************************************/

    /**
     * @param These $these
     * @param Individu $individu
     * @param $type
     * @return QueryBuilder
     */
    public function createQueryBuilder(These $these, Individu $individu, $type)
    {
        $qb = $this->getValidationService()->getEntityManager()->getRepository(Validation::class)->createQueryBuilder('validation')
            ->addSelect('type')->join('validation.typeValidation', 'type')
            ->andWhere('validation.histoDestruction is null')
            ->andWhere('type.code = :codeEngagement')
            ->andWhere('validation.these = :these')
            ->andWhere('validation.individu = :individu')
            ->setParameter('codeEngagement', $type)
            ->setParameter('these', $these)
            ->setParameter('individu', $individu)
            ;
        return $qb;
    }

    /**
     * @param These $these
     * @return Validation[] ==> clef: id de l'individu ayant validé <==
     */
    public function getEngagmentsImpartialiteByThese(These $these)
    {
        $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these);
        $engagements = [];
        foreach ($validations as $validation) {
            $engagements[$validation->getIndividu()->getId()] = $validation;
        }
        return $engagements;
    }

    /**
     * @param These $these
     * @param Membre $membre
     * @return Validation
     */
    public function getEngagementImpartialiteByMembre(These $these, Membre $membre)
    {
        if ($membre === null OR $membre->getActeur() === null) return null;
        $individu = $membre->getIndividu();
        $qb = $this->createQueryBuilder($these, $individu, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE);

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", 0, $e);
        }
        return $validation;
    }

    /**
     * @param These $these
     * @param Membre $membre
     * @return Validation
     */
    public function getRefusEngagementImpartialiteByMembre(These $these, Membre $membre)
    {
        $individu = $membre->getIndividu();
        $qb = $this->createQueryBuilder($these, $individu, TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE);

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs refus engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", 0,  $e);
        }
        return $validation;
    }



}