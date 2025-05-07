<?php
namespace Admission\Entity\Db\Repository;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;
use Individu\Entity\Db\Individu;
use Laminas\Mvc\Controller\AbstractActionController;

class AdmissionRepository extends DefaultEntityRepository
{
    /**
     * Recherche d'un dossier d'Admission à partir de son individu.
     *
     * @param Individu|string $individu
     * @return Admission|null
     */
    public function findOneByIndividu(Individu|string $individu): Admission|null
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.individu = :individu')->setParameter('individu', $individu)
            ->andWhereNotHistorise('a');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new \RuntimeException("Anomalie : plus d'1 admission trouvée pour cet individu", null, $e);
        }
    }

    /**
     * Recherche d'un dossier d'Admission lié à un doctorant.
     */
    public function findOneByDoctorant(Doctorant|string $doctorant): Admission|null
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.doctorant', 'd')->addSelect('d')
            ->where('d = :doctorant')->setParameter('doctorant', $doctorant)
            ->andWhereNotHistorise('a');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new \RuntimeException("Anomalie : plus d'1 admission trouvée pour ce doctorant", null, $e);
        }
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Admission|null
     */
    public function findRequestedAdmission(AbstractActionController $controller, string $param='admission') : ?Admission
    {
        $admissionId = $controller->params()->fromRoute($param);

        return $admissionId ? $this->find($admissionId) : null;
    }

    public function findOneById(int $id){
        return $this->find($id);
    }

    public function findOneByNumeroCandidature(string $numeroCandidature): Admission|null
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.individu', 'i')->addSelect('i')
            ->join('i.source', 'isrc')->addSelect('isrc')
            ->where('a.numeroCandidature = :numeroCandidature')->setParameter('numeroCandidature', $numeroCandidature)
            ->andWhereNotHistorise('a');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new \RuntimeException("Anomalie : plus d'1 admission trouvée pour ce numéro de candidature", null, $e);
        }
    }
}