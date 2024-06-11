<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Fichier\Entity\Db\NatureFichier;
use Doctrine\ORM\Query\Expr;


class DocumentRepository extends DefaultEntityRepository{

    public function findDocumentsByAdmission($id): array
    {
        return $this->findBy(['admission' => $id]);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByAdmissionAndNature(Admission $admission, NatureFichier|string $nature)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->join('d.fichier', 'f') // Supposons que la relation vers Fichier s'appelle "fichier"
            ->where('d.admission = :admission_id')
            ->setParameter('admission_id', $admission->getId());

        if ($nature !== null) {
            if (!$nature instanceof NatureFichier) {
                    $queryBuilder->join("f.nature", "n", Expr\Join::WITH, "n.code=:nature");
            } else {
                $queryBuilder->andWhere("f.nature = :nature");
            }
            $queryBuilder->setParameter("nature", $nature);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findOneWhereNoFichierByAdmission($admission)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.fichier', 'f') // Utilisation de leftJoin pour récupérer les entités sans fichier
            ->where('d.admission = :admission_id')
            ->andWhere('d.fichier IS NULL') // Utilisation de IS NULL pour filtrer les résultats sans fichier
            ->setParameter('admission_id', $admission->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    public function fetchNatureFichier($code): ?NatureFichier
    {
        /** @var ?NatureFichier $nature */
        $nature = $this->getEntityManager()->getRepository(NatureFichier::class)->findOneBy(['code' => $code]);
        return $nature;
    }
}