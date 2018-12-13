<?php

namespace Soutenance\Service\Proposition;

//TODO faire le repo aussi
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Service\Validation\ValidationService;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PropositionService {
    use EntityManagerAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ->andWhere("proposition.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [".$id."] ont été trouvées !");
        }
        return $result;
    }

    /**
     * @param These $these
     * @return Proposition
     */
    public function findByThese($these) {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ->andWhere("proposition.these = :these")
            ->setParameter("these", $these)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la thèse [".$these->getId()."] ont été trouvées !");
        }
        return $result;
    }


    /**
     * @return Proposition[]
     */
    public function findAll() {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Proposition $proposition
     */
    public function update($proposition)
    {
        try {
            $this->getEntityManager()->flush($proposition);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
    }

    public function findMembre($idMembre)
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->andWhere("membre.id = :id")
            ->setParameter("id", $idMembre)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiple membres sont associés à l'identifiant [".$idMembre."] !");
        }
        return $result;
    }

    public function create($proposition)
    {
        $this->getEntityManager()->persist($proposition);
        try {
            $this->getEntityManager()->flush($proposition);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un erreur s'est produite lors de l'enregistrment en BD de la proposition de thèse !");
        }
    }

    /**
     * @param Proposition $proposition
     * @return Membre[]
     */
    public function getRapporteurs($proposition) {
        $rapporteurs = [];
        /** @var Membre $membre */
        foreach ($proposition->getMembres() as $membre) {
            if($membre->getRole() === 'Rapporteur') $rapporteurs[] = $membre;
        }
        return $rapporteurs;
    }

    /**
     * Fonction annulant toutes les validations associés à la proposition de soutenances
     *
     * @param Proposition $proposition
     */
    public function annulerValidations($proposition)
    {
        $these = $proposition->getThese();
        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
            $this->getNotifierService()->triggerDevalidationProposition($validation);
        }
        $validationED = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_ED, $these));
        if ($validationED) {
            $this->getValidationService()->historise($validationED);
            $this->getNotifierService()->triggerDevalidationProposition($validationED);
        }
        $validationUR = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_UR, $these));
        if ($validationUR) {
            $this->getValidationService()->historise($validationUR);
            $this->getNotifierService()->triggerDevalidationProposition($validationUR);
        }
        $validationBDD = current($this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $these));
        if ($validationBDD) {
            $this->getValidationService()->historise($validationBDD);
            $this->getNotifierService()->triggerDevalidationProposition($validationBDD);
        }
    }
}