<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeStructure;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Structure\StructureServiceAwareTrait;
use Application\Service\StructureDocument\StructureDocumentServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Laminas\View\Model\ViewModel;

class StructureController extends AbstractController
{
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
                $this->redirect()->toRoute('ecole-doctorale/information', ['structure' => $id], [], true);
                break;
            case TypeStructure::CODE_UNITE_RECHERCHE :
                $unite = $this->getUniteRechercheService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('unite-recherche/information', ['structure' => $id], [], true);
                break;
            case TypeStructure::CODE_ETABLISSEMENT :
                $unite = $this->getEtablissementService()->getRepository()->findByStructureId($id);
                $this->getRoleService()->addRoleByStructure($unite);
                $this->redirect()->toRoute('etablissement/information', ['structure' => $id], [], true);
                break;
        }
    }

    /** GESTION DES DOCUMENTS LIES AUX STRUCTURES *********************************************************************/

    public function televerserDocumentAction()
    {
        $structure = $this->getStructureService()->getRequestedStructure($this);
        $natures = [$this->natureFichierService->getRepository()->findOneBy(['code' => NatureFichier::CODE_SIGNATURE_CONVOCATION]) ];
        $etablissements = $this->getEtablissementService()->getRepository()->findAllEtablissementsInscriptions();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            /** @var Etablissement|null $etablissement */
            $etablissement = null;
            if ($data['etablissement'] !== 'Aucun') $etablissement = $this->getEtablissementService()->getRepository()->find($data['etablissement']);
            /** @var NatureFichier $nature */
            $nature = $this->natureFichierService->getRepository()->find($data['nature']);

            $files = $request->getFiles()->toArray();
            $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
            $this->fichierService->saveFichiers($fichiers);

            $this->getStructureDocumentService()->addDocument($structure, $etablissement, $nature, $fichiers[0]);
        }

        $vm =  new ViewModel([
            'title' => "Ajout d'un document lié à la structure",
            'action' => $this->url()->fromRoute('structure/televerser-document', ['structure' => $structure->getId()], [], true),
            'natures' => $natures,
            'etablissements' => $etablissements,
        ]);
        $vm->setTemplate('application/structure/televerser-document');
        return $vm;
    }

    public function supprimerDocumentAction()
    {
        /** @var Etablissement $etablissement */
        $structure = $this->getStructureService()->getRequestedStructure($this);
        $document = $this->getStructureDocumentService()->getRequestedStructureDocument($this);
        $this->getStructureDocumentService()->historise($document);

        return $this->redirect()->toRoute($structure->getTypeStructure()->getCode() ."/information", ['structure' => $structure->getId()], ['fragment' => 'documents'], true);
    }
}