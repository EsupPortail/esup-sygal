<?php

namespace Admission\Service\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Repository\AdmissionRepository;
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
     * @throws NotSupported
     */
    public function findIfCurrentUserAlreadyHasAdmission(){
        $userId = $this->userContextService->getIdentityDb()->getId();
        $admission = $this->getRepository()->findOneByIndividu($userId);
        if($admission !== null){
            return true;
        }
        return false;
    }

    /**
     * @param Admission $admission
     * @return Admission
     */
    public function create(Admission $admission) : Admission
    {
        try {
            $date = new DateTime();
//            $user = $this->userContextService->getIdentityDb();
//            $admission->setHistoModification($date);
//            $admission->setHistoModificateur($user);
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
            $date = new DateTime();
//            $user = $this->userContextService->getIdentityDb();
//            $admission->setHistoModification($date);
//            $admission->setHistoModificateur($user);
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
     * @return Admission
     */
    public function delete(Admission $admission) : Admission
    {
        try {
            $this->getEntityManager()->remove($admission);
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de la suppression en base d'un Admission");
        }

        return $admission;
    }

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Admission
     */
    public function getRequestedAdmission(AbstractActionController $controller, string $param='Admission')
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Admission $admission */
        $admission = $this->getRepository()->find($id);
        return $admission;
    }
}