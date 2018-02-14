<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Form\EcoleDoctoraleForm;
use Application\RouteMatch;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareInterface;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Service\LdapPeopleServiceAwareInterface;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class EcoleDoctoraleController extends AbstractController
    implements EcoleDoctoraleServiceAwareInterface, LdapPeopleServiceAwareInterface, IndividuServiceAwareInterface
{
    use EcoleDoctoraleServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * L'index récupére la liste des écoles doctorales et la liste des individus associés à une école
     * doctorale si celle-ci est selectionnée.
     * @return \Zend\Http\Response|ViewModel
     *
     * RMQ: les résultats sont paginés.
     * TODO on affiche pas la partie basse de la pagination ce qui bloque l'acces aux pages suivantes ...
     */
    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        // récupération des écoles doctorales et pagination
        $qb = $this->ecoleDoctoraleService->getRepository()->createQueryBuilder('ed');
        $qb->addOrderBy('ed.libelle');
        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber(1);

        // récupération de la liste des individus de l'école doctorale séléctionnée
        $ecoleDoctoraleIndividus = null;
        if ($selected) {
            /** @var EcoleDoctorale $ed */
            $ed = $this->ecoleDoctoraleService->getRepository()->find($selected);
            $ecoleDoctoraleIndividus = $ed->getEcoleDoctoraleIndividus();
        }

        return new ViewModel([
            'ecoles'                  => $paginator,
            'selected'                => $selected,
            'ecoleDoctoraleIndividus' => $ecoleDoctoraleIndividus,
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
            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {
                /** @var EcoleDoctorale $ecole */
                $ecole = $this->ecoleDoctoraleForm->getData();
                $this->ecoleDoctoraleService->create($ecole, $this->userContextService->getIdentityDb());

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

    public function ajouterIndividuAction()
    {
        $ecoleId = $this->params()->fromRoute('ecoleDoctorale');
        $data = $this->params()->fromPost('people');

        if (!empty($data['id'])) {
            /** @var People $people */
            if ($people = $this->ldapPeopleService->get($data['id'])) {
                $supannEmpId = $people->get('supannEmpId');
                $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $supannEmpId]);
                if (! $individu) {
                    $individu = $this->individuService->createFromPeople($people);
                }
                /** @var EcoleDoctorale $ecole */
                $ecole = $this->ecoleDoctoraleService->getRepository()->find($ecoleId);

                $edi = $this->ecoleDoctoraleService->addIndividu($individu, $ecole);

                $this->flashMessenger()->addSuccessMessage("$individu est désormais membre de '$ecole' avec le rôle '{$edi->getRole()}'");
            }
        }

        return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecoleId]], true);
    }

    public function retirerIndividuAction()
    {
        $ediId = $this->params()->fromRoute('edi');

        if ($ediId) {
            $edi = $this->ecoleDoctoraleService->removeIndividu($ediId);

            $this->flashMessenger()->addSuccessMessage("{$edi->getIndividu()} n'est plus membre de '{$edi->getEcole()}'");

            return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $edi->getEcole()->getId()]], true);
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
     */
    public function ajouterLogoEcoleDoctorale($cheminLogoUploade)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        $ecole      = $this->requestEcoleDoctorale();
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
     *
     * TODO ne pas mettre les fichiers dans public problème de sécurité du fait que le répertoire est en écriture
     */
    static public function getLogoFilename(EcoleDoctorale $ecole, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= APPLICATION_DIR;
        $chemin .= "/public/Logos/ED/".$ecole->getSourceCode()."-".$ecole->getSigle().".png";
        return $chemin;
    }
}