<?php

namespace Soutenance\Service\EngagementImpartialite;

use Acteur\Service\ActeurHDR\ActeurHDRServiceAwareTrait;
use Acteur\Service\ActeurThese\ActeurTheseServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Membre;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceAwareTrait;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\ValidationHDR;
use Validation\Entity\Db\ValidationThese;

class EngagementImpartialiteService
{
    use ValidationTheseServiceAwareTrait;
    use ActeurTheseServiceAwareTrait;
    use ActeurHDRServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Membre $membre
     * @param These|HDR $entity
     * @return ValidationThese|ValidationHDR
     */
    public function create(Membre $membre, These|HDR $entity)
    {
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $validationService = $entity instanceof These ? $this->validationTheseService : $this->validationHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        $validation = $validationService->create(TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE, $entity, $acteur?->getIndividu());
        return $validation;
    }

    /**
     * @param Membre $membre
     * @param These|HDR $entity
     * @return ValidationThese|ValidationHDR
     */
    public function createRefus(Membre $membre, These|HDR $entity)
    {
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $validationService = $entity instanceof These ? $this->validationTheseService : $this->validationHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        $validation = $validationService->create(TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE, $entity, $acteur?->getIndividu());
        return $validation;
    }

    /**
     * @param Membre $membre
     * @return ValidationThese|ValidationHDR
     */
    public function delete(Membre $membre)
    {
        $entity = $membre->getProposition()->getObject();
        $validationService = $entity instanceof These ? $this->validationTheseService : $this->validationHDRService;

        $validation = $this->getEngagementImpartialiteByMembre($entity, $membre);
        $validation = $validationService->historiser($validation);
        return $validation;
    }

    /** REQUETE *******************************************************************************************************/

    public function createQueryBuilder(These|HDR $entity, Individu $individu, string $typeValidation): QueryBuilder
    {
        $validationService = $entity instanceof These ? $this->validationTheseService : $this->validationHDRService;

        $qb = $validationService->getRepository()->createQueryBuilder('validation')
            ->addSelect('v')->join('validation.validation', 'v')->andWhereNotHistorise('v')
            ->addSelect('type')->join('v.typeValidation', 'type')
            ->andWhere('validation.histoDestruction is null')
            ->andWhere('type.code = :codeEngagement')
            ->andWhere('validation.individu = :individu')
            ->setParameter('codeEngagement', $typeValidation)
            ->setParameter('individu', $individu)
            ;
        if($entity instanceof These) {
            $qb->andWhere('validation.these = :these')
            ->setParameter('these', $entity);

        }else{
            $qb->andWhere('validation.hdr = :hdr')
                ->setParameter('hdr', $entity);
        }
        return $qb;
    }

    /**
     * @param These|HDR $entity
     * @param Membre[] $rapporteurs
     * @return ValidationThese[]|ValidationHDR[] ==> clef: id de l'individu ayant validé <==
     */
    public function getEngagmentsImpartialiteByEntity(These|HDR $entity, array $rapporteurs): array
    {
//        $rapporteursIndividuIds = array_map(fn(Membre $m) => $m->getActeur()?->getIndividu()?->getId(), $rapporteurs);
        $rapporteursIndividuIds = array_map(function(Membre $m) {
            $acteurService =  $m->getProposition()->getObject() instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
            $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($m);
            return $acteur?->getIndividu()?->getId();
        }, $rapporteurs);

        $validationService = $entity instanceof These ? $this->validationTheseService : $this->validationHDRService;

        $qb = $validationService->getRepository()->createQueryBuilder('v')
            ->andWhere('i IN (:individuIds)')->setParameter('individuIds', $rapporteursIndividuIds)
            ->andWhere('t = :these')->setParameter('these', $entity)
            ->andWhere('tv.code = :code')->setParameter('code', TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE)
            ->andWhereNotHistorise('v');
        $validations = $qb->getQuery()->getResult();

        $engagements = [];
        foreach ($validations as $validation) {
            $engagements[$validation->getIndividu()->getId()] = $validation;
        }

        return $engagements;
    }

    /**
     * @param These|HDR $entity
     * @param Membre $membre
     * @return ValidationThese|ValidationHDR
     */
    public function getEngagementImpartialiteByMembre(These|HDR $entity, Membre $membre)
    {
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
//        if ($membre === null OR $membre->getActeur() === null) return null;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        if ($acteur === null) {
            return null;
        }

        $individu = $acteur->getIndividu();
        $qb = $this->createQueryBuilder($entity, $individu, TypeValidation::CODE_ENGAGEMENT_IMPARTIALITE);

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", 0, $e);
        }
        return $validation;
    }

    /**
     * @param These|HDR $entity
     * @param Membre $membre
     * @return ValidationThese|ValidationHDR
     */
    public function getRefusEngagementImpartialiteByMembre(These|HDR $entity, Membre $membre)
    {
        $acteurService = $entity instanceof These ? $this->acteurTheseService : $this->acteurHDRService;
        $acteur = $acteurService->getRepository()->findActeurForSoutenanceMembre($membre);
        $individu = $acteur?->getIndividu();
        $qb = $this->createQueryBuilder($entity, $individu, TypeValidation::CODE_REFUS_ENGAGEMENT_IMPARTIALITE);

        try {
            $validation = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs refus engagements d'impartialité ont été signé par le membre [".$individu->__toString()."].", 0,  $e);
        }
        return $validation;
    }
}