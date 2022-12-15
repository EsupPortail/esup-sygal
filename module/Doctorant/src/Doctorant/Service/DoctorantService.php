<?php

namespace Doctorant\Service;

use Application\Entity\Db\Utilisateur;
use Application\Entity\UserWrapper;
use Application\Service\BaseService;
use Application\SourceCodeStringHelperAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Entity\Db\Repository\DoctorantRepository;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;

class DoctorantService extends BaseService
{
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

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
            ->andWhere('individu.id = :id')->setParameter('id', $user->getIndividu()->getId());
        ;

        /** @var Doctorant $result */
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs doctorants sont liés au mếme individu [".$user->getIndividu()->getId()."]");
        }
        return $result;


    }
}