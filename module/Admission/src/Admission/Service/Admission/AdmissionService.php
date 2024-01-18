<?php

namespace Admission\Service\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Etudiant;
use Admission\Entity\Db\Financement;
use Admission\Entity\Db\Inscription;
use Admission\Entity\Db\Repository\AdmissionRepository;
use Admission\Service\Avis\AdmissionAvisServiceAwareTrait;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Etudiant\EtudiantServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Validation\AdmissionValidationServiceAwareTrait;
use Application\Application\Form\Hydrator\IndividuRecrutementObject;
use Application\Service\BaseService;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Source\SourceServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\ORMException;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;

class AdmissionService extends BaseService
{
    use RoleServiceAwareTrait;
    use SourceServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use UserContextServiceAwareTrait;
    use EtudiantServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use AdmissionValidationServiceAwareTrait;
    use DocumentServiceAwareTrait;
    use AdmissionAvisServiceAwareTrait;

    /**
     * @return AdmissionRepository
     * @throws NotSupported
     */
    public function getRepository()
    {
        /** @var AdmissionRepository $repo */
        $repo = $this->entityManager->getRepository(Admission::class);

        return $repo;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function create(Admission $admission) : Admission
    {
        try {
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }

        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function update(Admission $admission)  :Admission
    {
        try {
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }

        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function historise(Admission $admission)  :Admission
    {
        try {
            $admission->historiser();
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }
        return $admission;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function restore(Admission $admission)  :Admission
    {
        try {
            $admission->dehistoriser();
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }
        return $admission;
    }

    /**
     * @param Admission $admission
     * @return void
     * @throws ORMException
     */
    public function delete(Admission $admission) : void
    {
        $this->getEntityManager()->beginTransaction();

        try {
            $etudiant = $admission->getEtudiant()->first();
            if($etudiant instanceof Etudiant){
                $this->etudiantService->delete($etudiant);
            }

            $inscription = $admission->getInscription()->first();
            if($inscription instanceof Inscription){
                $this->inscriptionService->delete($inscription);
            }

            $financement = $admission->getFinancement()->first();
            if($financement instanceof Financement){
                $this->financementService->delete($financement);
            }

            $this->documentService->deleteAllDocumentsForAdmission($admission);
            $this->admissionValidationService->deleteValidationForAdmission($admission);
            $this->admissionAvisService->deleteAllAvisForAdmission($admission);

            $this->getEntityManager()->remove($admission);
            $this->getEntityManager()->flush($admission);

            // commit
            $this->commit();
        }
        catch (ORMException $e) {
            $this->rollBack();
            throw $e;
        }
    }
}