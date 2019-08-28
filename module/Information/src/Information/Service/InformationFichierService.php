<?php

namespace Information\Service;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\NatureFichier;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InformationFichierService
{
    use EntityManagerAwareTrait;

    /**
     * @return Fichier[]
     */
    public function getInformationFichiers()
    {
        $qb = $this->getEntityManager()->getRepository(Fichier::class)->createQueryBuilder('fichier')
            ->addSelect('createur')
            ->join('fichier.histoCreateur', 'createur')
            ->join('fichier.nature', 'nat', Expr\Join::WITH, 'nat.code = :code')
            ->setParameter('code', NatureFichier::CODE_DIVERS)
            ->orderBy('fichier.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param integer $id
     * @return Fichier
     */
    public function getInformationFichier($id)
    {
        $qb = $this->getEntityManager()->getRepository(Fichier::class)->createQueryBuilder('fichier')
            ->andWhere('fichier.id = :id')
            ->setParameter('id', $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieur Fichier partagent le même identifiant [" . $id . "]", $e);
        }

        return $result;
    }
}