<?php

namespace Soutenance\Service\Validation;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\ValidationRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ValidationService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    /**
     * @return ValidationRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Validation::class);
    }

    /**
     * Fetch le type de validation spécifié.
     *
     * @param string $code
     * @return null|TypeValidation
     */
    public function getTypeValidation($code)
    {
        return $this->entityManager->getRepository(TypeValidation::class)->findOneBy(['code' => $code]);
    }


    /**
     * @param string $type
     * @param These $these
     * @param Individu $individu
     * @param Utilisateur|null $utilisateur
     * @return Validation
     */
    public function create($type, $these, $individu, $utilisateur = null) {
        $v = new Validation(
            $this->getTypeValidation($type),
            $these,
            $individu);
        if ($utilisateur !== null) {
            $v->setHistoCreateur($utilisateur);
            $v->setHistoModificateur($utilisateur);
        }


        $this->getEntityManager()->persist($v);
        try {
            $this->getEntityManager()->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }
        return $v;
    }

    /**
     * @param Validation $validation
     * @return Validation
     */
    public function historiser($validation)
    {
        /** @var Utilisateur $user */
        $user = $this->userContextService->getIdentityDb();
        /** @var DateTime $date */
        try {
            $date = new DateTime();
        } catch (\Exception $e) {
            throw new RuntimeException("Problème lors de la récupération de la date", $e);
        }
        $validation->setHistoDestructeur($user);
        $validation->setHistoDestruction($date);

        try {
            $this->getEntityManager()->flush($validation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'historisation en base d'un Validation",$e);
        }

        return $validation;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validatePropositionSoutenance($these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_PROPOSITION_SOUTENANCE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }

        return $v;
    }

    public function unvalidatePropositionSoutenance($these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise();
        /** @var Validation $v */
        try {
            $v = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Anomalie: plus d'une validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        try {
            $this->getEntityManager()->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'historisation de la validation en bdd", null, $e);
        }

        return $v;
    }

    /**
     * @var These $these
     * @var Individu $individu
     * @return Validation
     */
    public function findValidationPropositionSoutenanceByTheseAndIndividu($these, $individu) {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
            ->andWhere("v.individu = :individu")
            ->setParameter("individu", $individu);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs validations pour un même acteur et une même thèse.");
        }
        return $result;
    }

    /**
     * @var These $these
     * @return Validation[]
     */
    public function findValidationPropositionSoutenanceByThese($these) {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Validation $validation
     */
    public function historise($validation) {
        $validation->historiser();
        try {
            $this->getEntityManager()->flush($validation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'historisation");
        }
    }

    /**
     * @param Validation $validation
     * @return  Validation
     */
    public function unsignEngagementImpartialite($validation)
    {
        $validation->historiser();

        try {
            $this->entityManager->flush($validation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'historisation de la validation en bdd", null, $e);
        }
        return $validation;
    }

    public function validateValidationUR($these, $individu)
    {
        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_VALIDATION_PROPOSITION_UR),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }

        return $v;

    }

    public function validateValidationED($these, $individu)
    {
        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_VALIDATION_PROPOSITION_ED),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }

        return $v;

    }

    public function validateValidationBDD($these, $individu)
    {
        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }

        return $v;

    }

    public function signerAvisSoutenance($these, $individu, $sygal = null)
    {
        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_AVIS_SOUTENANCE),
            $these,
            $individu);
        if ($sygal === true) {
            $sygal = $this->getUtilisateurService()->getRepository()->findByUsername('sygal-app');
            $v->setHistoCreateur($sygal);
            $v->setHistoModificateur($sygal);
        }

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de la signature de l'avis de soutanance", null, $e);
        }

        return $v;

    }
}