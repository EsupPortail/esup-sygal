<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\NatureFichier;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeStructure;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Fichier\FichierServiceAwareTrait;
use Application\Service\NatureFichier\NatureFichierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class EtablissementController
 */
class EtablissementController extends StructureConcreteController
{
    use EtablissementServiceAwareTrait;
    use FichierServiceAwareTrait;
    use NatureFichierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    protected $codeTypeStructure = TypeStructure::CODE_ETABLISSEMENT;

    /**
     * @var string
     */
    protected $routeName = 'etablissement';

    /**
     * @return EtablissementService
     */
    protected function getStructureConcreteService()
    {
        return $this->etablissementService;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $etablissements = $this->structureService->getAllStructuresAffichablesByType($this->codeTypeStructure, 'libelle');

        $etablissementsPrincipaux = array_filter($etablissements, function (Etablissement $e) {
            return $e->estMembre();
        });
        $etablissementsExternes = array_filter($etablissements, function (Etablissement $e) {
            return !$e->estMembre();
        });

        return new ViewModel([
            'etablissementsSygal'    => $etablissementsPrincipaux,
            'etablissementsExternes' => $etablissementsExternes,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function informationAction()
    {
        $id = $this->params()->fromRoute('structure');
        /** @var Etablissement $etablissement */
        $etablissement = $this->getStructureConcreteService()->getRepository()->findByStructureId($id);
        if ($etablissement === null) {
            throw new RuntimeException("Aucun établissement ne possède l'identifiant renseigné.");
        }

        $roleListings = [];
        $individuListings = [];
        $roles = $this->roleService->getRolesByStructure($etablissement->getStructure());
        $individus = $this->roleService->getIndividuByStructure($etablissement->getStructure());
        $individuRoles = $this->roleService->getIndividuRoleByStructure($etablissement->getStructure());

        /** @var Role $role */
        foreach ($roles as $role) {
            if (!$role->isTheseDependant()) {
                $roleListings [$role->getLibelle()] = 0;
            }
        }

        /** @var Individu $individu */
        foreach ($individus as $individu) {
            $denomination = $individu->getNomComplet(false, false, false, true, false);
            $individuListings[$denomination] = [];
        }

        /** @var IndividuRole $individuRole */
        foreach ($individuRoles as $individuRole) {
            if (!$individuRole->getRole()->isTheseDependant()) {
                $denomination = $individuRole->getIndividu()->getNomComplet(false, false, false, true, false);
                $role = $individuRole->getRole()->getLibelle();
                $individuListings[$denomination][] = $role;
                $roleListings[$role]++;
            }
        }

        return new ViewModel([
            'etablissement'   => $etablissement,
            'roleListing'     => $roleListings,
            'individuListing' => $individuListings,
            'logoContent'     => $this->structureService->getLogoStructureContent($etablissement),
            'signatureConvocation' => $this->etablissementService->getSignatureConvocationContent($etablissement),
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function modifierAction()
    {
        $viewModel = parent::modifierAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     */
    public function ajouterAction()
    {
        $viewModel = parent::ajouterAction();

        if ($viewModel instanceof Response) {
            return $viewModel;
        }

        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;
    }

    /** gestion des signatures */
    public function televerserSignatureConvocationAction()
    {
        $structure = $this->getStructureService()->getRequestedStructure($this);
        $etablissement = $this->getStructureConcreteService()->getRepository()->findByStructureId($structure->getId());
        $nature = $this->natureFichierService->getRepository()->findOneBy(['code' => NatureFichier::CODE_SIGNATURE_CONVOCATION]);
        $request = $this->getRequest();
        if ($request->isPost()) {
                $files = $request->getFiles()->toArray();
                $fichiers = $this->fichierService->createFichiersFromUpload(['files' => $files], $nature);
                $this->fichierService->saveFichiers($fichiers);
                $etablissement->setSignatureConvocation($fichiers[0]);
                $this->getEtablissementService()->update($etablissement);
        }

        $vm =  new ViewModel([
            'title' => "Ajout d'une signature pour les convocation pour l'établissement [".$structure->getLibelle()."]",
            'nature' => 'SIGNATURE_CONVOCATION',
            'action' => $this->url()->fromRoute('etablissement/televerser-signature-convocation', ['structure' => $structure->getId()], [], true),
        ]);
        $vm->setTemplate('application/etablissement/televerser-document');
        return $vm;
    }

    public function supprimerSignatureConvocationAction()
    {
        /** @var Etablissement $etablissement */
        $structure = $this->getStructureService()->getRequestedStructure($this);
        $etablissement = $this->getStructureConcreteService()->getRepository()->findByStructureId($structure->getId());
        $this->fichierService->supprimerFichiers([$etablissement->getSignatureConvocation()]);
        $etablissement->setSignatureConvocation(null);
        $this->etablissementService->update($etablissement);

        return $this->redirect()->toRoute($this->routeName."/information", [], ['query' => ['selected' => $structure->getId()], "fragment" => $structure->getId()], true);
    }
}