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

    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        $qb = $this->uniteRechercheService->getRepository()->createQueryBuilder('ed');
        $qb->addOrderBy('ed.libelle');

        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage(50)
            ->setCurrentPageNumber(1);

        $uniteRechercheIndividus = null;
        if ($selected) {
            /** @var UniteRecherche $ed */
            $ed = $this->uniteRechercheService->getRepository()->find($selected);
            $uniteRechercheIndividus = $ed->getUniteRechercheIndividus();
        }

        return new ViewModel([
            'unites'                  => $paginator,
            'selected'                => $selected,
            'uniteRechercheIndividus' => $uniteRechercheIndividus,
        ]);
    }

    public function modifierAction()
    {
        $unite = $this->requestUniteRecherche();
        $this->uniteRechercheForm->bind($unite);

        if ($data = $this->params()->fromPost()) {
            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {
                /** @var UniteRecherche $unite */
                $unite = $this->uniteRechercheForm->getData();
                $this->uniteRechercheService->update($unite);

                $this->flashMessenger()->addSuccessMessage("Unité de recherche '$unite' modifiée avec succès");

                return $this->redirect()->toRoute('unite-recherche', [], ['query' => ['selected' => $unite->getId()]], true);
            }
        }

        $this->uniteRechercheForm->setAttribute('action',
            $this->url()->fromRoute('unite-recherche/modifier', [], ['uniteRecherche' => $unite->getUniteRechercheIndividus()], true));

        $viewModel = new ViewModel([
            'form' => $this->uniteRechercheForm,
        ]);
        $viewModel->setTemplate('application/unite-recherche/modifier');

        return $viewModel;
    }

    public function ajouterAction()
    {
        if ($data = $this->params()->fromPost()) {
            $this->uniteRechercheForm->setData($data);
            if ($this->uniteRechercheForm->isValid()) {
                /** @var UniteRecherche $unite */
                $unite = $this->uniteRechercheForm->getData();
                $this->uniteRechercheService->create($unite, $this->userContextService->getIdentityDb());

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
}