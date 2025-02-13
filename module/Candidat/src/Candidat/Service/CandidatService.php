<?php

namespace Candidat\Service;

use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Candidat\Entity\Db\Candidat;
use Candidat\Entity\Db\Repository\CandidatRepository;
use Candidat\Entity\Db\Repository\DoctorantRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\NonUniqueResultException;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class CandidatService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @return CandidatRepository
     */
    public function getRepository(): CandidatRepository
    {
        /** @var CandidatRepository $repo */
        $repo = $this->entityManager->getRepository(Candidat::class);

        return $repo;
    }

    /**
     * @param string|null $term
     * @return Candidat[]
     */
    public function getCandidatsByTerm(?string $term = null) : array
    {
        $seach = '%' . strtolower($term) . '%';
        $qb = $this->getRepository()->createQueryBuilder('candidat')
            ->join('candidat.individu', 'individu')->addSelect('individu')
            ->andWhere("concat(concat(concat(concat(lower(individu.prenom1), ' '), lower(individu.nomUsuel)), ' '), candidat.ine) like :search")
            ->setParameter('search', $seach);

        return $qb->getQuery()->getResult();
    }

    public function getCandidatsByUser(?Utilisateur $user) : ?Candidat
    {
        if ($user === null OR $user->getIndividu() === null) return null;

        $qb = $this->getEntityManager()->getRepository(Candidat::class)->createQueryBuilder('candidat')
            ->leftJoin('candidat.individu','individu')
            ->andWhere('individu.id = :id')->setParameter('id', $user->getIndividu()->getId())
            ->andWhere('individu.histoDestruction IS NULL')
            ->andWhere('candidat.histoDestruction IS NULL')
        ;

        /** @var Candidat $result */
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs candidats sont liés au mếme individu [".$user->getIndividu()->getId()."]");
        }
        return $result;


    }

    /**
     * Enregistre le Candidat en base de données.
     */
    public function saveCandidat(Candidat $candidat): void
    {
        try {
            $this->entityManager->persist($candidat);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement du nouvel individu", null, $e);
        }
    }

    public function newCandidat(Individu $individu, Etablissement $etablissement): Candidat
    {
        try {
            $etablissement = $this->etablissementService->getRepository()->find($etablissement->getId());
        } catch (RuntimeException $e) {
            throw new RuntimeException("Aucun établissement de trouvé avec cet id", null, $e);
        }

        $candidat = new Candidat();
        $candidat->setIndividu($individu);
        $candidat->setEtablissement($etablissement);
        $candidat->setSource($this->sourceService->fetchApplicationSource());
        $candidat->setSourceCode($this->sourceService->genereateSourceCode());

        return $candidat;
    }

    /**
     * On recherche un candidat dont le SOURCE_CODE égale '{PREFIX_ETAB}::{supannId}, où :
     * - {PREFIX_ETAB} (ex : 'INSA') : code de l'établissement/structure dont le domaine égale celui extrait de l'EPPN (ex: 'insa-rouen.fr') ;
     * - {supannId} (ex : '000020533') : supannEtuId, supannEmpId ou autre (cf. {@see \UnicaenAuthentification\Service\ShibService::extractShibUserIdValueForDomainFromShibData()}
     *   issue des données d'identité.
     *
     * @param UserWrapper $user
     * @return Candidat|null
     */
    public function findOneByUserWrapper(UserWrapper $user): ?Candidat
    {
        if ($individu = $user->getIndividu()) {
            return $this->getRepository()->findOneByIndividu($individu);
        }

        $id = $user->getSupannId();
        if (! $id) {
//            throw new RuntimeException("Aucun id supann disponible.");
            return null;
        }

        $domaineEtab = $user->getDomainFromEppn();
        if (! $domaineEtab) {
            return null;
        }

        $etablissement = $this->getEtablissementService()->getRepository()->findOneByDomaine($domaineEtab);
        if (! $etablissement) {
            return null;
        }

        $sourceCode = $this->sourceCodeStringHelper->addEtablissementPrefixTo($id, $etablissement);

        return $this->getRepository()->findOneBySourceCode($sourceCode);
    }
}