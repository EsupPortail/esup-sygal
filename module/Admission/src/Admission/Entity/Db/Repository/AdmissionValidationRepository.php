<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\TypeValidation;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class AdmissionValidationRepository extends DefaultEntityRepository{

    public function findAdmissionValidationByTypeValidationAndAdmission(Admission $admission, TypeValidation $typeValidation){
        $qb = $this->createQueryBuilder('aV')
            ->where('aV.admission = :admission')->setParameter('admission', $admission)
            ->andWhere('aV.typeValidation = :typeValidation')->setParameter('typeValidation', $typeValidation)
            ->andWhereNotHistorise('aV');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new \RuntimeException("Anomalie : plus d'1 validation a été trouvée pour ce dossier d'admission/type validation", null, $e);
        }
    }
}