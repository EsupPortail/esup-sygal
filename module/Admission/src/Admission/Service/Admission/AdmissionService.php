<?php

namespace Admission\Service\Admission;

use Admission\Entity\Db\Admission;
use Admission\Entity\Db\Repository\AdmissionRepository;
use Admission\Service\Document\DocumentServiceAwareTrait;
use Admission\Service\Financement\FinancementServiceAwareTrait;
use Admission\Service\Individu\IndividuServiceAwareTrait;
use Admission\Service\Inscription\InscriptionServiceAwareTrait;
use Admission\Service\Validation\ValidationServiceAwareTrait;
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
    use IndividuServiceAwareTrait;
    use InscriptionServiceAwareTrait;
    use FinancementServiceAwareTrait;
    use ValidationServiceAwareTrait;
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

    public function findIfCurrentUserAlreadyHasAdmission(){
        $userId = $this->userContextService->getIdentityDb()->getId();
        $admission = $this->getRepository()->findOneByIndividuId($userId);
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
            $user = $this->userContextService->getIdentityDb();
            $admission->setHistoModification($date);
            $admission->setHistoModificateur($user);
            $this->getEntityManager()->persist($admission);
            $this->getEntityManager()->flush($admission);
        } catch(ORMException $e) {
            throw new RuntimeException("Un problème est survenue lors de l'enregistrement en base d'un Admission");
        }

        return $admission;
    }

    public function ajouter(Admission $admissionObject)
    {
        var_dump($admissionObject);
        $admissionIndividu = $admissionObject->getIndividu()->first();
        var_dump($admissionIndividu);
        $admissionInscription = $admissionObject->getInscription()->first();
        $admissionFinancement = $admissionObject->getFinancement()->first();

        try {

            if ($admissionIndividu !== null) {
                // Si la création du recrutement porte sur un individu existant, création inutile.
                $individu = $admissionIndividu;
            } else {
                $individu = $this->individuAdmissionService->create($admissionIndividu, $admissionObject);
            }
            $inscription = $this->inscriptionService->create($admissionInscription,$admissionObject);
            $financement = $this->financementService->create($admissionFinancement,$admissionObject);

            $this->create($admissionObject);

            $this->commit();

        }
        catch (Exception $e) {
            throw $e;
        }

        return $admissionObject;
    }

    /**
     * Mise à jour d'un IndividuRecrutement
     * en BDD à partir d'une instance de {@see IndividuRecrutementObject}
     * hydratée par {@see IndividuRecrutementHydrator}.
     *
     * @param Admission $admissionObject
     * @throws Exception
     */
    public function modifier(Admission $admissionObject)
    {

        // Récupérez l'EntityManager de Doctrine
        $entityManager = $this->getEntityManager();
        var_dump($admissionObject);
        // Récupérez l'entité Admission associée à l'EntityManager
        $admissionEntity = $entityManager->find(Admission::class, $admissionObject->getId());

        $admissionIndividu = $admissionEntity->getIndividu()[0];
        $admissionInscription = $admissionEntity->getInscription()[0];
        $admissionFinancement = $admissionEntity->getFinancement()[0];
//        $admissionValidation = $admissionEntity->getValidation()[0];
//        $admissionDocuments = $admissionEntity->getDocument()[0];



        $this->beginTransaction();

        try {
            $this->individuAdmissionService->update($admissionIndividu);
            $this->inscriptionService->update($admissionInscription);
            $this->financementService->update($admissionFinancement);
//            $this->validationService->update($admissionValidation);
//            $this->documentService->update($admissionDocuments);

            $this->update($admissionObject);

            $this->commit();
        }
        catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }



    /**
     * @param Admission $admission
     * @return Admission
     */
    public function update(Admission $admission)  :Admission
    {
        try {
            $date = new DateTime();
            $user = $this->userContextService->getIdentityDb();
            $admission->setHistoModification($date);
            $admission->setHistoModificateur($user);
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