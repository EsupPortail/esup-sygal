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

    private $etablissementForm;

    /**
     * @param EtablissementForm $form
     * @return $this
     */
    public function setEcoleDoctoraleForm(EtablissementForm $form)
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
                $this->etablissementForm->create($etablissement, $this->userContextService->getIdentityDb());

                $this->flashMessenger()->addSuccessMessage("Établissement '$etablissement' créée avec succès");

                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
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
//        $this->ecoleDoctoraleService->deleteSoftly($ecole, $this->userContextService->getIdentityDb());
//        $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' supprimée avec succès");

        return $this->redirect()->toRoute('etablissement', [], ['query' => ['selected' => $etablissement->getId()]], true);
    }

    public function ajouterIndividuAction() {
        $viewModel = new ViewModel([
            //'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/etablissement/index');

        return $viewModel;
    }

    public function retirerIndividuAction() {
        $viewModel = new ViewModel([
            //'form' => $this->ecoleDoctoraleForm,
        ]);
        $viewModel->setTemplate('application/etablissement/index');

        return $viewModel;
    }


    public function modifierAction() {
        return new ViewModel();
    }
}