<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Service\UserContextServiceAwareTrait;
use Individu\Entity\Db\Individu;

class IndividuRepository extends DefaultEntityRepository{
    public function findIfCurrentUserHasAlreadyAdmission(): bool
    {
        $individu = $this->findOneByIndividuId(4);
        if($individu !== null){
            return true;
        }
        return false;
    }

    /**
     * @param Individu $individu
     * @return Individu[]
     */
    public function findAdmissionByDoctorant(Individu $individu): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('th.individu', 'i')
            ->andWhere('i = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('t.histoDestruction is null')
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return Admission[]
     */
    public function findAdmissionByIndividu(Individu $individu): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('th.individu', 'i')
            ->andWhere('i = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('t.histoDestruction is null')
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;
        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche d'un fieldset Individu à partir de l'ID de son créateur.
     *
     * @param string $id
     * @return Individu
     */
    public function findOneByIndividuId($id){
        return $this->findOneBy(['individuId' => $id]);
    }
}