<?php

namespace RapportActivite\Service\Validation;

use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use Application\Service\BaseService;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class RapportActiviteValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use TypeValidationAwareTrait;

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(RapportActiviteValidation::class);
    }

    /**
     * @param RapportActivite $rapportActivite
     * @return RapportActiviteValidation
     */
    public function createForRapportActivite(RapportActivite $rapportActivite): RapportActiviteValidation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new RapportActiviteValidation(
            $this->typeValidation,
            $rapportActivite,
            $individu);

        try {
            $this->entityManager->persist($v);
            $this->entityManager->flush($v);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }

        return $v;
    }

    /**
     * @param RapportActivite $rapportActivite
     * @return RapportActiviteValidation|null
     */
    public function findByRapportActivite(RapportActivite $rapportActivite): ?RapportActiviteValidation
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->join('v.type', 't', Join::WITH, 't = :type')->setParameter('type', $this->typeValidation)
            ->join('v.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('r', $rapportActivite)
            ->andWhere('v.histoDestruction is null');
        try {
            /** @var RapportActiviteValidation $rapportValidation */
            $rapportValidation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(sprintf(
                "Plusieures validations non historisées de type '%s' trouvées pour le rapport '%s'",
                $this->typeValidation->getCode(),
                $rapportActivite
            ));
        }

        return $rapportValidation;
    }

    /**
     * @param RapportActiviteValidation $rapportValidation
     */
    public function delete(RapportActiviteValidation $rapportValidation)
    {
        $rapportValidation->historiser();
        try {
            $this->getEntityManager()->flush($rapportValidation);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    /**
     * Supprime la validation du rapport.
     *
     * @param RapportActivite $rapportActivite
     */
    public function deleteValidationForRapportActivite(RapportActivite $rapportActivite)
    {
        // NB : il peut y avoir des validations historisées
        foreach ($rapportActivite->getRapportValidations() as $validation) {
            $rapportActivite->removeRapportValidation($validation);
            try {
                $this->getEntityManager()->remove($validation);
                $this->getEntityManager()->flush($validation);
            } catch (ORMException $e) {
                throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
            }
        }
    }
}