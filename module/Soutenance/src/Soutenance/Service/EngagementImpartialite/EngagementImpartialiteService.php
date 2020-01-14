<?php

namespace Soutenance\Service\EngagementImpartialite;

use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Doctrine\ORM\NonUniqueResultException;
use Soutenance\Entity\Membre;
use Soutenance\Service\Validation\ValidatationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class EngagementImpartialiteService {
    use ValidatationServiceAwareTrait;
    /**
     * @param These $these
     * @return Validation[] ==> clef: id de l'individu ayant fait la validation <==
     */
    public function getEngagmentsImpartialiteByThese($these)
    {
        $validations = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these);
        $engagements = [];
        foreach ($validations as $validation) {
            $engagements[$validation->getIndividu()->getId()] = $validation;
        }
        return $engagements;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function getEngagementImpartialiteByMembre($membre)
    {
        if ($membre === null OR $membre->getActeur() === null) return null;
        $individu = $membre->getIndividu();
        $these = $membre->getActeur()->getThese();

        $qb = $this->getValidationService()->getEntityManager()->getRepository(Validation::class)->createQueryBuilder('validation')
            ->addSelect('type')->join('validation.typeValidation', 'type')
            ->andWhere('type.code = :codeEngagement')
            ->andWhere('validation.these = :these')
            ->andWhere('validation.individu = :individu')
            ->andWhere('1 = pasHistorise(validation)')
            ->setParameter('codeEngagement', TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE)
            ->setParameter('these', $these)
            ->setParameter('individu', $individu)
            ;

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", $e);
        }
        return $validation;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function getRefusEngagementImpartialiteByMembre($membre)
    {
        $individu = $membre->getIndividu();
        $these = $membre->getActeur()->getThese();

        $qb = $this->getValidationService()->getEntityManager()->getRepository(Validation::class)->createQueryBuilder('validation')
            ->addSelect('type')->join('validation.typeValidation', 'type')
            ->andWhere('type.code = :codeEngagement')
            ->andWhere('validation.these = :these')
            ->andWhere('validation.individu = :individu')
            ->andWhere('1 = pasHistorise(validation)')
            ->setParameter('codeEngagement', TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE)
            ->setParameter('these', $these)
            ->setParameter('individu', $individu)
        ;

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs refus engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", $e);
        }
        return $validation;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function createEngagementImpartialite($membre)
    {
        $individu = $membre->getIndividu();
        $these = $membre->getActeur()->getThese();

        $validation = $this->getValidationService()->create(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $these, $individu);

        return $validation;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function createRefusEngagementImpartialite($membre)
    {
        $individu = $membre->getIndividu();
        $these = $membre->getActeur()->getThese();

        $validation = $this->getValidationService()->create(TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE, $these, $individu);

        return $validation;
    }

    /**
     * @param Membre $membre
     * @return Validation
     */
    public function deleteEngagementImpartialite($membre)
    {
        $validation = $this->getEngagementImpartialiteByMembre($membre);
        $validation = $this->getValidationService()->historiser($validation);

        return $validation;
    }

}