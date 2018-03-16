<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Application\Form\EcoleDoctoraleForm;
use Application\RouteMatch;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class EcoleDoctoraleController extends AbstractController
{
    use EcoleDoctoraleServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * L'index récupére :
     * - la liste des écoles doctorales
     * - l'école doctorale sélectionnée
     * - la liste des rôles associées à l'école
     * - un tableau de tableaux des rôles associés à chaque rôle
     * @return \Zend\Http\Response|ViewModel
     *
     * TODO transformer effectifs en tableau associatif (rôle => liste de membres)
     */
    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');
        $ecoles = $this->ecoleDoctoraleService->getEcolesDoctorales();
        usort($ecoles, function(EcoleDoctorale $a, EcoleDoctorale $b) {return $a->getLibelle() > $b->getLibelle();});

        $roles = null;
        $effectifs = null;
        if ($selected) {
            /**
             * @var EcoleDoctorale $ecole
             * @var Role[] $roles
             */
            $ecole  = $this->ecoleDoctoraleService->getEcoleDoctoraleById($selected);
            $roles = $ecole->getStructure()->getStructureDependantRoles();

            $effectifs = [];
            foreach ($roles as $role) {
                $individus = $this->individuService->getIndividuByRole($role);
                $effectifs[] = $individus;
            }
        }

        return new ViewModel([
            'ecoles'                  => $ecoles,
            'selected'                => $selected,
            'roles'                   => $roles,
            'effectifs'               => $effectifs,
        ]);
    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une école doctorale (Sigle, Libellé et Logo)
     *
     * @return \Zend\Http\Response|ViewModel
     *
     * TODO en cas de changement de SIGLE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        /** @var EcoleDoctorale $ecole */
        $ecole = $this->requestEcoleDoctorale();
        $this->ecoleDoctoraleForm->bind($ecole);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoEcoleDoctorale();
                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
            }

            // action de modification
            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEcoleDoctorale($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux écoles doctorales
                $ecole = $this->ecoleDoctoraleForm->getData();
                $this->ecoleDoctoraleService->update($ecole);

                $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' modifiée avec succès");
                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/ecole-doctorale/modifier');
        return $viewModel;
    }

    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {
                /** @var EcoleDoctorale $ecole */
                $ecole = $this->ecoleDoctoraleForm->getData();
                $ecole = $this->ecoleDoctoraleService->create($ecole, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEcoleDoctorale($file['cheminLogo']['tmp_name'], $ecole);
                }

                $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' créée avec succès");

                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
            }
        }

        $this->ecoleDoctoraleForm->setAttribute('action', $this->url()->fromRoute('ecole-doctorale/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/ecole-doctorale/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $ecole = $this->requestEcoleDoctorale();

        $this->ecoleDoctoraleService->deleteSoftly($ecole, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' supprimée avec succès");

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
    }

    public function restaurerAction()
    {
        $ecole = $this->requestEcoleDoctorale();

        $this->ecoleDoctoraleService->undelete($ecole);

        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' restaurée avec succès");

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
    }

    /**
     * Ajout des individus et de leurs rôles dans la table INDIVIDU_ROLE
     * @return \Zend\Http\Response
     */
    public function ajouterIndividuAction()
    {
        $edId       = $this->params()->fromRoute('ecoleDoctorale');
        $data       = $this->params()->fromPost('people');
        $roleId     = $this->params()->fromPost('role');

        if (!empty($data['id'])) {
            /** @var People $people */
            if ($people = $this->ldapPeopleService->get($data['id'])) {
                $supannEmpId = $people->get('supannEmpId');
                $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $supannEmpId]);
                if (! $individu) {
                    $individu = $this->individuService->createFromPeople($people);
                }

                /**
                 * @var EcoleDoctorale $ecole
                 * @var Role $role
                 * @var IndividuRole $individuRole
                 */
                $ecole = $this->ecoleDoctoraleService->getEcoleDoctoraleById($edId);
                $role = $this->roleService->getRoleById($roleId);
                $individuRole = $this->roleService->addIndividuRole($individu,$role);

                $this->flashMessenger()->addSuccessMessage(
                    "<strong>{$individuRole->getIndividu()}</strong>". " est désormais " .
                    "<strong>{$individuRole->getRole()}</strong>". " de l'école doctorale ".
                    "<strong>{$ecole->getLibelle()}</strong>.");
            }
        }

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $edId]], true);
    }

    /**
     * Retrait des individus et de leurs rôles dans la table INDIVIDU_ROLE
     * @return \Zend\Http\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function retirerIndividuAction()
    {
        $edId = $this->params()->fromRoute('ecoleDoctorale');
        $irId = $this->params()->fromRoute('edi');

        $ecole = null;
        if ($edId !== null) {
            $ecole = $this->ecoleDoctoraleService->getEcoleDoctoraleById($edId);
        }

        if ($irId) {
            $individuRole = $this->roleService->removeIndividuRoleById($irId);

            $this->flashMessenger()->addSuccessMessage(
                "<strong>{$individuRole->getIndividu()}</strong>" . " n'est plus n'est plus "
                ."<strong>{$individuRole->getRole()}</strong>" . " de l'école doctorale "
                ."<strong>{$ecole->getLibelle()}</strong>"."</strong>");

            return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $edId]], true);
        }

        return $this->redirect()->toRoute('ecole-doctorale', [], [], true);
    }

    /**
     * @return EcoleDoctorale
     */
    private function requestEcoleDoctorale()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getEcoleDoctorale();
    }

    /**
     * @var EcoleDoctoraleForm
     */
    private $ecoleDoctoraleForm;

    /**
     * @param EcoleDoctoraleForm $form
     * @return $this
     */
    public function setEcoleDoctoraleForm(EcoleDoctoraleForm $form)
    {
        $this->ecoleDoctoraleForm = $form;

        return $this;
    }

    /**
     * Retire le logo associé à une école doctorale:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoEcoleDoctorale()
    {
        $ecole = $this->requestEcoleDoctorale();

        $this->ecoleDoctoraleService->deleteLogo($ecole);
        $filename   = EcoleDoctoraleController::getLogoFilename($ecole, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$ecole->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une école doctorale:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/ED/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     * @param EcoleDoctorale $ecole
     */
    public function ajouterLogoEcoleDoctorale($cheminLogoUploade, EcoleDoctorale $ecole = null)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        if ($ecole === null) $ecole      = $this->requestEcoleDoctorale();
        $chemin     = EcoleDoctoraleController::getLogoFilename($ecole, false);
        $filename   = EcoleDoctoraleController::getLogoFilename($ecole, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$ecole->getLibelle()} vient d'être ajouté.");
            $this->ecoleDoctoraleService->setLogo($ecole,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'école doctorale <strong>{$ecole->getLibelle()}.</strong>");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une école doctorale
     * @param EcoleDoctorale $ecole
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'école doctorale $ecole
     */
    static public function getLogoFilename(EcoleDoctorale $ecole, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= APPLICATION_DIR;
        $chemin .= "/ressources/Logos/ED/".$ecole->getSourceCode()."-".$ecole->getSigle().".png";
        return $chemin;
    }
}