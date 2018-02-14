<?php

namespace Application\Controller;

use Application\Entity\Db\EcoleDoctorale;
use Application\Form\EcoleDoctoraleForm;
use Application\QueryBuilder\DefaultQueryBuilder;
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

    public function indexAction()
    {
        $selected = $this->params()->fromQuery('selected');

        /** @var DefaultQueryBuilder $qb */
        $qb = $this->ecoleDoctoraleService->getRepository()->createQueryBuilder('ed');
//        $qb->andWhereNotHistorise();
        $qb->addOrderBy('ed.libelle');

        $paginator = new \Zend\Paginator\Paginator(new DoctrinePaginator(new Paginator($qb, true)));
        $paginator
            ->setPageRange(30)
            ->setItemCountPerPage(20)
            ->setCurrentPageNumber(1);

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

    public function modifierAction()
    {
        $ecole = $this->requestEcoleDoctorale();
        $this->ecoleDoctoraleForm->bind($ecole);

        if ($data = $this->params()->fromPost()) {
            if (isset($data['supprimer-logo'])) {
                $this->supprimerLogoEcoleDoctorale();
            } else {
                var_dump($data);
                $this->ajouterLogoEcoleDoctorale();
            }

            $this->ecoleDoctoraleForm->setData($data);
            if ($this->ecoleDoctoraleForm->isValid()) {

                var_dump("formulaire valide");
                /** @var EcoleDoctorale $ecole */
                $ecole = $this->ecoleDoctoraleForm->getData();
                $this->ecoleDoctoraleService->update($ecole);


                $this->flashMessenger()->addSuccessMessage("École doctorale '$ecole' modifiée avec succès");

                return $this->redirect()->toRoute('ecole-doctorale', [], ['query' => ['selected' => $ecole->getId()]], true);
            }

        }

        $this->ecoleDoctoraleForm->setAttribute('action',
            $this->url()->fromRoute('ecole-doctorale/modifier', [], ['ecoleDoctorale' => $ecole->getEcoleDoctoraleIndividus()], true));

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

    public function supprimerLogoEcoleDoctorale()
    {
        $ecole = $this->requestEcoleDoctorale();
        $this->ecoleDoctoraleService->deleteLogo($ecole);

        $this->flashMessenger()->addSuccessMessage("Le logo de l'école doctorale {$ecole->getLibelle()} vient d'être supprimé");
        return $this->redirect()->toRoute('ecole-doctorale', [], [], true);
    }

    public function ajouterLogoEcoleDoctorale()
    {
        $ecole = $this->requestEcoleDoctorale();
        $chemin = "/public/Logos/ED/".$ecole->getSourceCode()."-".$ecole->getSigle().".png";
        $this->ecoleDoctoraleService->setLogo($ecole,$chemin);

        //return $this->redirect()->toRoute('ecole-doctorale', [], [], true);
    }
}