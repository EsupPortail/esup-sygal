<?php

namespace Structure\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\TypeStructure;
use Structure\Provider\Privilege\StructurePrivileges;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use Structure\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;

class StructureController extends AbstractController
{
    const TAB_infos = 'informations';
    const TAB_membres = 'membres';
    const TAB_docs = 'documents';
    const TAB_coenc = 'coencadrants';

    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use StructureServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use FichierServiceAwareTrait;
    use StructureDocumentServiceAwareTrait;

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $consultationToutes = $this->isAllowed(
            StructurePrivileges::getResourceId(StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES),
            StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES);

        $structures = [];
        if ($consultationToutes) {
            $structures = $this->getStructureService()->getAllStructuresAffichablesByType(TypeStructure::CODE_ECOLE_DOCTORALE, 'libelle');
        } else {
            /** @var Role $role*/
            $role = $this->userContextService->getSelectedIdentityRole();
            if ($role->isEcoleDoctoraleDependant()) {
                $ecole = $this->getUniteRechercheService()->getRepository()->findByStructureId($role->getStructure()->getId());
                $structures[] = $ecole;
            }
        }

        return new ViewModel([
            'structures' => $structures,
        ]);
    }

    public function individuRoleAction()
    {
        $structureId = $this->params()->fromRoute("structure");
        $structure = $this->structureService->findStructureById($structureId);
        $type = $this->params()->fromRoute("type");

        $roles_tmp = $this->roleService->getRolesByStructure($structure);
        $roles = [];
        /** @var Role $role */
        foreach ($roles_tmp as $role) {
            if (!$role->isTheseDependant()) $roles[] = $role;
        }

        $individuRoles = $this->roleService->getIndividuRoleByStructure($structure);

        $repartition = [];
        foreach ($roles as $role) {
            $repartition[$role->getId()] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            $role = $individuRole->getRole();
            $individu = $individuRole->getIndividu();
            $repartition[$role->getId()][] = $individu;
        }

        $membres = [];
        foreach ($repartition as $role => $individus) {
            $membres = array_merge($membres, $individus);
        }
        $membres = array_unique($membres);

        return new ViewModel([
            'roles' => $roles,
            'membres' => $membres,
            'repartition' => $repartition,
            'type' => $type,
        ]);
    }


    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function genererRolesDefautsAction() {
        $id   = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type');

        switch($type) {
            case TypeStructure::CODE_ECOLE_DOCTORALE :
                $ecole = $this->getEcoleDoctoraleService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($ecole);
                return $this->redirect()->toRoute('ecole-doctorale/information', ['structure' => $id], ['query' => ['tab' => StructureController::TAB_infos]], true);
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                return $this->redirect()->toRoute('unite-recherche/information', ['structure' => $id], ['query' => ['tab' => StructureController::TAB_infos]], true);
            case TypeStructure::CODE_ETABLISSEMENT :
                $unite = $this->getEtablissementService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                return $this->redirect()->toRoute('etablissement/information', ['structure' => $id], ['query' => ['tab' => StructureController::TAB_infos]], true);
        }
    }

    /** GESTION DES DOCUMENTS LIES AUX STRUCTURES *********************************************************************/

    public function televerserDocumentAction(): ViewModel
    {
        $structure = $this->structureService->getRequestedStructure($this);
        $natures = $this->natureFichierService->findAllByCodes([
            NatureFichier::CODE_SIGNATURE_CONVOCATION,
            NatureFichier::CODE_SIGNATURE_RAPPORT_ACTIVITE,
        ]);
        $etablissements = $this->etablissementService->getRepository()->findAllEtablissementsInscriptions();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['etablissement'] === 'null') $data['etablissement'] = null;
            /** @var Etablissement|null $etablissement */
            $etablissement = null;
            if ($data['etablissement'] !== null ) $etablissement = $this->etablissementService->getRepository()->find($data['etablissement']);
            /** @var NatureFichier $nature */
            $nature = $this->natureFichierService->getRepository()->find($data['nature']);

            $files = $request->getFiles()->toArray();
            $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
            $this->fichierService->saveFichiers($fichiers);
            $this->structureDocumentService->addDocument($structure, $etablissement, $nature, $fichiers[0]);
        }

        $vm =  new ViewModel([
            'title' => "Ajout d'un document lié à la structure",
            'action' => $this->url()->fromRoute('structure/televerser-document', ['structure' => $structure->getId()], ['query' => ['tab' => StructureController::TAB_docs]], true),
            'natures' => $natures,
            'etablissements' => $etablissements,
        ]);
        $vm->setTemplate('structure/structure/televerser-document');

        return $vm;
    }

    public function supprimerDocumentAction(): Response
    {
        /** @var Etablissement $etablissement */
        $structure = $this->getStructureService()->getRequestedStructure($this);
        $document = $this->getStructureDocumentService()->getRequestedStructureDocument($this);
        $this->getStructureDocumentService()->historise($document);

        return $this->redirect()->toRoute($structure->getTypeStructure()->getCode() ."/information", ['structure' => $structure->getId()], ['query' => ['tab' => StructureController::TAB_docs]], true);
    }
}