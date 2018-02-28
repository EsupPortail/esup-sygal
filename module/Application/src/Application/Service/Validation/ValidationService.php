<?php

namespace Application\Service\Validation;

use Application\Entity\Db\Repository\ValidationRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Entity\Db\VSitu\DepotVersionCorrigeeValidationDirecteur;
use Application\Service\BaseService;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;

class ValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;

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
     * Recherche s'il existe des validations RDV BU historisées.
     *
     * @param These $these
     * @return bool
     */
    public function existsValidationRdvBuHistorisee(These $these)
    {
        return (bool) $these->getValidation(TypeValidation::CODE_RDV_BU, true);
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateRdvBu(These $these)
    {
        $v = new Validation($this->getTypeValidation(TypeValidation::CODE_RDV_BU), $these);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     */
    public function unvalidateRdvBu(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhere('tv.code = :tvcode')
            ->andWhere('v.these = :these')
            ->andWhere('pasHistorise(v) = 1')
            ->setParameter('these', $these)
            ->setParameter('tvcode', TypeValidation::CODE_RDV_BU);
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", TypeValidation::CODE_RDV_BU, $these));
        }

        $v->historiser();

        $this->getEntityManager()->flush($v);

        // si la validation est annulée, on remet le témoin de page de couv conforme à faux
        if ($rdvBu = $these->getRdvBu()) {
            $rdvBu->setPageTitreConforme(-1);

            $this->getEntityManager()->flush($rdvBu);
        }
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateDepotTheseCorrigee(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateVersionPapierCorrigee(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_VERSION_PAPIER_CORRIGEE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateDepotTheseCorrigee(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_DEPOT_THESE_CORRIGEE)
            ->andWhereNotHistorise();
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        $this->getEntityManager()->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateCorrectionThese(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_CORRECTION_THESE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateCorrectionThese(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_CORRECTION_THESE)
            ->andWhereNotHistorise();
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        $this->getEntityManager()->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return DepotVersionCorrigeeValidationDirecteur[]
     */
    public function getValidationsAttenduesPourCorrectionThese(These $these)
    {
        $qb = $this->getEntityManager()->getRepository(DepotVersionCorrigeeValidationDirecteur::class)->createQueryBuilder('va')
            ->addSelect('t, i')
            ->join('va.these', 't', Join::WITH, 't = :these')
            ->join('va.individu', 'i')
            ->andWhere('va.valide = 0')
            ->setParameter('these', $these);

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}