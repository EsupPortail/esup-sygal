<?php

namespace Application\Controller;

use Application\Entity\Db\Etablissement;
use Application\Form\EtablissementForm;
use Application\QueryBuilder\DefaultQueryBuilder;
use Application\Service\Etablissement\EtablissementServiceAwareInterface;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenLdap\Service\LdapPeopleServiceAwareInterface;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class EtablissementController extends AbstractController
    implements EtablissementServiceAwareInterface, LdapPeopleServiceAwareInterface, IndividuServiceAwareInterface
{
    use EtablissementServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * @var EtablissementForm $etablissementForm
     */
    private $etablissementForm;

    /**
     * @param EtablissementForm $form
     * @return $this
     */
    public function setEtablissementForm(EtablissementForm $form)
    {
        $this->etablissementForm = $form;

        return $this;
    }

    /**
     * @return Etablissement
     */
    private function requestEtablissement()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getEtablissement();
    }


    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        /** la pagination est desactivée pour le moment */
        /** @var DefaultQueryBuilder $qb */
        $qb = $this->etablissementService->getRepository()->createQueryBuilder('ed');
        $qb->addOrderBy('ed.libelle');
        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber(1);

        /** les individus sont desactivés pour le moment */
        $etablissementIndividus = null;
        if ($selected) {
            /** @var Etablissement $etab */
            $etab = $this->etablissementService->getRepository()->find($selected);
            $etablissementIndividus = [];//$etab->getEtablissementIndividus();
        }

        return new ViewModel([
            'etablissements' => $paginator,
            'selected' => $selected,
            'etablissementIndividus' => $etablissementIndividus,

        ]);
    }

    public function ajouterAction() {

        if ($data = $this->params()->fromPost()) {
            $this->etablissementForm->setData($data);
            if ($this->etablissementForm->isValid()) {
                /** @var Etablissement $etablissement */
                $etablissement = $this->etablissementForm->getData();
                $this->etablissementService->create($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' créée avec succès");

                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
            }
        }

        $this->etablissementForm->setAttribute('action', $this->url()->fromRoute('etablissement/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $etablissement = $this->requestEtablissement();
        $this->etablissementService->delete($etablissement);
        $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' supprimé avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
    }

    public function modifierAction() {
        /** @var Etablissement $etablissement */
        $etablissement = $this->requestEtablissement();
        $this->etablissementForm->bind($etablissement);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoEtablissement();
                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
            }

            // action de modification
            $this->etablissementForm->setData($data);
            if ($this->etablissementForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoEtablissement($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux écoles doctorales
                $etablissement = $this->etablissementForm->getData();
                $this->etablissementService->update($etablissement);

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' modifiée avec succès");
                return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->etablissementForm,
        ]);
        $viewModel->setTemplate('application/etablissement/modifier');
        return $viewModel;

    }

    /**
     * Retire le logo associé à une établissement:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoEtablissement()
    {
        $etablissement = $this->requestEtablissement();

        $this->etablissementService->deleteLogo($etablissement);
        $filename   = EtablissementController::getLogoFilename($etablissement, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$etablissement->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'établissement <strong>{$etablissement->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'établissement <strong>{$etablissement->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une établissement:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/Etab/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     */
    public function ajouterLogoEtablissement($cheminLogoUploade)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        $etablissement  = $this->requestEtablissement();
        $chemin         = EtablissementController::getLogoFilename($etablissement, false);
        $filename       = EtablissementController::getLogoFilename($etablissement, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'établissement {$etablissement->getLibelle()} vient d'être ajouté.");
            $this->etablissementService->setLogo($etablissement,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'établissement <strong>{$etablissement->getLibelle()}.</strong>");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une établissement
     * @param Etablissement $etablissement
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'établissement $etablissement
     */
    static public function getLogoFilename(Etablissement $etablissement, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= APPLICATION_DIR;
        $chemin .= "/ressources/Logos/Etab/".$etablissement->getCode().".png";
        return $chemin;
    }


//    public function ajouterIndividuAction() {
//        $viewModel = new ViewModel([
//            'form' => $this->etablissementFormForm,
//        ]);
//        $viewModel->setTemplate('application/etablissement/index');
//
//        return $viewModel;
//    }

//    public function retirerIndividuAction() {
//        $viewModel = new ViewModel([
//            'form' => $this->etablissementForm,
//        ]);
//        $viewModel->setTemplate('application/etablissement/index');
//
//        return $viewModel;
//    }

}