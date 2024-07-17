<?php

namespace Doctorant\Service;

use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\Repository\DoctorantRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use RuntimeException;
use Structure\Entity\Db\Etablissement;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class DoctorantService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use SourceServiceAwareTrait;

    /**
     * @return DoctorantRepository
     */
    public function getRepository(): DoctorantRepository
    {
        /** @var DoctorantRepository $repo */
        $repo = $this->entityManager->getRepository(Doctorant::class);

        return $repo;
    }

    /**
     * On recherche un doctorant dont le SOURCE_CODE égale '{PREFIX_ETAB}::{supannId}, où :
     * - {PREFIX_ETAB} (ex : 'INSA') : code de l'établissement/structure dont le domaine égale celui extrait de l'EPPN (ex: 'insa-rouen.fr') ;
     * - {supannId} (ex : '000020533') : supannEtuId, supannEmpId ou autre (cf. {@see \UnicaenAuth\Service\ShibService::extractShibUserIdValueForDomainFromShibData()}
     *   issue des données d'identité.
     *
     * @param UserWrapper $user
     * @return Doctorant|null
     */
    public function findOneByUserWrapper(UserWrapper $user): ?Doctorant
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

    /**
     * @param string|null $term
     * @return Doctorant[]
     */
    public function getDoctorantsByTerm(?string $term = null) : array
    {
        $seach = '%' . strtolower($term) . '%';
        $qb = $this->getRepository()->createQueryBuilder('doctorant')
            ->join('doctorant.individu', 'individu')->addSelect('individu')
            ->andWhere("concat(concat(concat(concat(lower(individu.prenom1), ' '), lower(individu.nomUsuel)), ' '), doctorant.ine) like :search")
            ->setParameter('search', $seach);

        return $qb->getQuery()->getResult();
    }

    public function getDoctorantsByUser(?Utilisateur $user) : ?Doctorant
    {
        if ($user === null OR $user->getIndividu() === null) return null;

        $qb = $this->getEntityManager()->getRepository(Doctorant::class)->createQueryBuilder('doctorant')
            ->leftJoin('doctorant.individu','individu')
            ->andWhere('individu.id = :id')->setParameter('id', $user->getIndividu()->getId())
            ->andWhere('individu.histoDestruction IS NULL')
            ->andWhere('doctorant.histoDestruction IS NULL')
        ;

        /** @var Doctorant $result */
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants sont liés au mếme individu [".$user->getIndividu()->getId()."]");
        }
        return $result;


    }

    /**
     * Enregistre le Doctorant en base de données.
     */
    public function saveDoctorant(Doctorant $doctorant): void
    {
        try {
            $this->entityManager->persist($doctorant);
            $this->entityManager->flush();
        } catch (\Doctrine\ORM\Exception\ORMException $e) {
            throw new \UnicaenApp\Exception\RuntimeException("Erreur lors de l'enregistrement du nouvel individu", null, $e);
        }
    }

    public function newDoctorant(Individu $individu)
    {
        $etablissement = $this->etablissementService->getRepository()->find(1);

        $doctorant = new Doctorant();
        $doctorant->setIndividu($individu);
        $doctorant->setEtablissement($etablissement);
        $doctorant->setSource($this->sourceService->fetchApplicationSource());
        $doctorant->setSourceCode($this->sourceService->genereateSourceCode());

        return $doctorant;
    }
}