<?php

namespace Application\Controller;

use Application\Entity\Db\UniteRecherche;
use Application\Form\UniteRechercheForm;
use Application\RouteMatch;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareInterface;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use UnicaenLdap\Entity\People;
use UnicaenLdap\Service\LdapPeopleServiceAwareInterface;
use UnicaenLdap\Service\LdapPeopleServiceAwareTrait;
use Zend\View\Model\ViewModel;

class UniteRechercheController extends AbstractController
    implements UniteRechercheServiceAwareInterface, LdapPeopleServiceAwareInterface, IndividuServiceAwareInterface
{
    use UniteRechercheServiceAwareTrait;
    use LdapPeopleServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * L'index récupére la liste des unités de recherche et la liste des individus associés à une unité
     * de recherche si celle-ci est selectionnée.
     * @return \Zend\Http\Response|ViewModel
     *
     * RMQ: les résultats sont paginés.
     * TODO on affiche pas la partie basse de la pagination ce qui bloque l'acces aux pages suivantes ...
     */
    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        // récupération des unités de recherche et pagination
        $qb = $this->uniteRechercheService->getRepository()->createQueryBuilder('ed');
        $qb->addOrderBy('ed.libelle');
        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage(50)
            ->setCurrentPageNumber(1);

        // récupération de la liste des individus de l'unité de recherche séléctionnée
        $uniteRechercheIndividus = null;
        if ($selected) {
            /** @var UniteRecherche ur */
            $ur = $this->uniteRechercheService->getRepository()->find($selected);
            $uniteRechercheIndividus = $ur->getUniteRechercheIndividus();
        }

        return new ViewModel([
            'unites'                  => $paginator,
            'selected'                => $selected,
            'uniteRechercheIndividus' => $uniteRechercheIndividus,
        ]);
    }

    /**
     * Modifier permet soit d'afficher le formulaire associé à la modification soit de mettre à jour
     * les données associées à une unité de recherche (Sigle, Libellé, Code et Logo)
     *
     * @return \Zend\Http\Response|ViewModel
     *
     * TODO en cas de changement de SIGLE ou de CODE penser à faire un renommage du logo
     */
    public function modifierAction()
    {
        /** @var UniteRecherche $unite */
        $unite = $this->requestUniteRecherche();
        $this->uniteRechercheForm->bind($unite);

        // si POST alors on revient du formulaire
        if ($data = $this->params()->fromPost()) {

            // récupération des données et des fichiers
            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            // action d'affacement du logo
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoUniteRecherche();
                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
            }

            // action de modification
            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoUniteRecherche($file['cheminLogo']['tmp_name']);
                }
                // mise à jour des données relatives aux unités de recherche
                $unite = $this->uniteRechercheForm->getData();
                $this->uniteRechercheService->update($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' modifiée avec succès");
                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
            }
            $this->flashMessenger()->addErrorMessage("Echec de la mise à jour : données incorrectes saissie");
            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
        }

        // envoie vers le formulaire de modification
        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
        ]);
        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
    }

    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {

            $request = $this->getRequest();
            $data = $request->getPost()->toArray();
            $file = $request->getFiles()->toArray();

            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {
                /** @var UniteRecherche $unite */
                $unite = $this->uniteRechercheForm->getData();
                $this->uniteRechercheService->create($unite, $this->userContextService->getIdentityDb());

                // sauvegarde du logo si fourni
                if ($file['cheminLogo']['tmp_name'] !== '') {
                    $this->ajouterLogoUniteRecherche($file['cheminLogo']['tmp_name'], $unite);
                }

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' créée avec succès");

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
            }
        }

        $this->uniteRechercheForm->setAttribute('action', $this->url()->fromRoute('unite-recherche/ajouter'));

        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
        ]);
        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
    }

    public function supprimerAction()
    {
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->deleteSoftly($unite, $this->userContextService->getIdentityDb());

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' supprimée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
    }

    public function restaurerAction()
    {
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->undelete($unite);

        $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' restaurée avec succès");

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
    }

    public function ajouterIndividuAction()
    {
        $uniteId = $this->params()->fromRoute('uniteRecherche');
        $data = $this->params()->fromPost('people');

        if (!empty($data['id'])) {
            /** @var People $people */
            if ($people = $this->ldapPeopleService->get($data['id'])) {
                $supannEmpId = $people->get('supannEmpId');
                $individu = $this->individuService->getRepository()->findOneBy(['sourceCode' => $supannEmpId]);
                if (! $individu) {
                    $individu = $this->individuService->createFromPeople($people);
                }
                /** @var UniteRecherche $unite */
                $unite = $this->uniteRechercheService->getRepository()->find($uniteId);

                $edi = $this->uniteRechercheService->addIndividu($individu, $unite);

                $this->flashMessenger()->addSuccessMessage("$individu est désormais membre de '$unite' avec le rôle '{$edi->getRole()}'");
            }
        }

        return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $uniteId]], true);
    }

    public function retirerIndividuAction()
    {
        $ediId = $this->params()->fromRoute('edi');

        if ($ediId) {
            $edi = $this->uniteRechercheService->removeIndividu($ediId);

            $this->flashMessenger()->addSuccessMessage("{$edi->getIndividu()} n'est plus membre de '{$edi->getUniteRecherche()}'");

            return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $edi->getUniteRecherche()->getId()]], true);
        }

        return $this->redirect()->toRoute('unite-recherche', [], [], true);
    }

    /**
     * @return UniteRecherche
     */
    private function requestUniteRecherche()
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getUniteRecherche();
    }

    /**
     * @var UniteRechercheForm
     */
    private $uniteRechercheForm;

    /**
     * @param UniteRechercheForm $form
     * @return $this
     */
    public function setUniteRechercheForm(UniteRechercheForm $form)
    {
        $this->uniteRechercheForm = $form;

        return $this;
    }

    /**
     * Retire le logo associé à une unite de recherche:
     * - modification base de donnée (champ CHEMIN_LOG <- null)
     * - effacement du fichier stocké sur le serveur
     */
    public function supprimerLogoUniteRecherche()
    {
        $unite = $this->requestUniteRecherche();

        $this->uniteRechercheService->deleteLogo($unite);
        $filename   = UniteRechercheController::getLogoFilename($unite, true);
        if (file_exists($filename)) {
            $result = unlink($filename);
            if ($result) {
                $this->flashMessenger()->addSuccessMessage("Le logo de l'unité de recherche {$unite->getLibelle()} vient d'être supprimé.");
            } else {
                $this->flashMessenger()->addErrorMessage("Erreur lors de l'effacement du logo de l'unité de recherche <strong>{$unite->getLibelle()}.</strong>");
            }
        } else {
            $this->flashMessenger()->addWarningMessage("Aucun logo à supprimer pour l'unité de recherche <strong>{$unite->getLibelle()}.</strong>");
        }

    }

    /**
     * Ajoute le logo associé à une unité de recherche:
     * - modification base de donnée (champ CHEMIN_LOG <- /public/Logos/UR/LOGO_NAME)
     * - enregistrement du fichier sur le serveur
     * @param string $cheminLogoUploade     chemin vers le fichier temporaire associé au logo
     * @param UniteRecherche $unite
     */
    public function ajouterLogoUniteRecherche($cheminLogoUploade, UniteRecherche $unite = null)
    {
        if ($cheminLogoUploade === null || $cheminLogoUploade === '') {
            $this->flashMessenger()->addErrorMessage("Fichier logo invalide.");
            return;
        }

        if ($unite === null) $unite      = $this->requestUniteRecherche();
        $chemin     = UniteRechercheController::getLogoFilename($unite, false);
        $filename   = UniteRechercheController::getLogoFilename($unite, true);
        $result = rename($cheminLogoUploade, $filename);
        if ($result) {
            $this->flashMessenger()->addSuccessMessage("Le logo de l'unité de recherche {$unite->getLibelle()} vient d'être ajouté.");
            $this->uniteRechercheService->setLogo($unite,$chemin);
        } else {
            $this->flashMessenger()->addErrorMessage("Erreur lors de l'enregistrement du logo de l'unité de recherche <strong>{$unite->getLibelle()}</strong>.");
        }
    }

    /**
     * Retourne le chemin vers le logo d'une unité de recherche
     * @param UniteRecherche $unite
     * @param bool $fullpath            si true chemin absolue sinon chemin relatif au répertoire de l'application
     * @return string                   le chemin vers le logo de l'unité de recherche $ecole
     */
    static public function getLogoFilename(UniteRecherche $unite, $fullpath=true)
    {
        $chemin = "";
        if ($fullpath) $chemin .= APPLICATION_DIR;
        $chemin .= "/ressources/Logos/UR/".$unite->getSourceCode()."-".$unite->getSigle().".png";
        return $chemin;
    }
}