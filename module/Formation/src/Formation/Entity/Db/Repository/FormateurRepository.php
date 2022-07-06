<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Formation\Entity\Db\Formateur;
use Formation\Entity\Db\Session;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormateurRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Formateur|null
     */
    public function getRequestedFormateur(AbstractActionController $controller, string $param = 'formateur'): ?Formateur
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Formateur|null $formateur */
        $formateur = $this->find($id);
        return $formateur;
    }

    /**
     * @return \Formation\Entity\Db\Formateur[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('f')
            ->addSelect('s, i')
            ->join('f.session', 's', Join::WITH, 's.histoDestruction is null')
            ->join('f.individu', 'i', Join::WITH, 'i.histoDestruction is null')
            ->orderBy('i.nomUsuel, i.prenom1');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return \Formation\Entity\Db\Formateur[]
     */
    public function findAllForSession(Session $session): array
    {
        $qb = $this->createQueryBuilder('f')
            ->addSelect('s, i')
            ->join('f.session', 's', Join::WITH, 's.histoDestruction is null')
            ->join('f.individu', 'i', Join::WITH, 'i.histoDestruction is null')
            ->andWhere('s = :session')->setParameter('session', $session)
            ->orderBy('i.nomUsuel, i.prenom1');

        return $qb->getQuery()->getResult();
    }
}