<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Document;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\Exception\NotSupported;
use Fichier\Entity\Db\NatureFichier;
use Doctrine\ORM\Query\Expr;


class DocumentRepository extends DefaultEntityRepository{

    public function findDocumentsByAdmission($id): array
    {
        return $this->findBy(['admission' => $id]);
    }

    public function findByAdmissionAndNature($admission, $nature)
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

        return $queryBuilder->getQuery()->getSingleResult();
    }

    public function fetchNatureFichier($code): ?NatureFichier
    {
        /** @var ?NatureFichier $nature */
        $nature = $this->getEntityManager()->getRepository(NatureFichier::class)->findOneBy(['code' => $code]);
        return $nature;
    }
}