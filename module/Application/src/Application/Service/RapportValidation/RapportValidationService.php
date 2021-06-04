<?php

namespace Application\Service\RapportValidation;

use Application\Entity\Db\Interfaces\TypeValidationAwareTrait;
use Application\Entity\Db\Rapport;
use Application\Entity\Db\RapportValidation;
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

class RapportValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use TypeValidationAwareTrait;

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(RapportValidation::class);
    }

    /**
     * @param Rapport $rapport
     * @return RapportValidation
     */
    public function createForRapport(Rapport $rapport): RapportValidation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new RapportValidation(
            $this->typeValidation,
            $rapport,
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
     * @param Rapport $rapport
     * @return RapportValidation|null
     */
    public function findByRapport(Rapport $rapport): ?RapportValidation
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->join('v.type', 't', Join::WITH, 't = :type')->setParameter('type', $this->typeValidation)
            ->join('v.rapport', 'r', Join::WITH, 'r = :rapport')->setParameter('r', $rapport)
            ->andWhere('pasHistorise(v) = 1');
        try {
            /** @var RapportValidation $rapportValidation */
            $rapportValidation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(sprintf(
                "Plusieures validations non historisées de type '%s' trouvées pour le rapport '%s'",
                $this->typeValidation->getCode(),
                $rapport
            ));
        }

        return $rapportValidation;
    }

    /**
     * @param RapportValidation $rapportValidation
     */
    public function delete(RapportValidation $rapportValidation)
    {
        $rapportValidation->historiser();
        try {
            $this->getEntityManager()->flush($rapportValidation);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }
    }

    /**
     * SUpprime toutes les validations du rapport spécifié, peu importe leur type.
     *
     * @param Rapport $rapport
     */
    public function deleteAllForRapport(Rapport $rapport)
    {
        try {
            foreach ($rapport->getRapportValidations() as $validation) {
                $rapport->removeRapportValidation($validation);
                $this->getEntityManager()->remove($validation);
                $this->getEntityManager()->flush($validation);
            }
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de la suppression en bdd", null, $e);
        }
    }
}