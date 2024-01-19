<?php

namespace Individu\Controller\IndividuRole;

use Application\Controller\AbstractController;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Exception;
use Individu\Entity\Db\IndividuRole;
use Individu\Form\IndividuRole\IndividuRoleForm;
use Individu\Service\IndividuRole\IndividuRoleServiceAwareTrait;
use InvalidArgumentException;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Webmozart\Assert\Assert;

class IndividuRoleController extends AbstractController
{
    use IndividuRoleServiceAwareTrait;

    private IndividuRoleForm $form;

    /*****************************************************************************
     *
     * TODO : les méthodes de {@see \Application\Controller\UtilisateurController}
     *        qui manipule des IndividuRole devrait être déplacées ici !
     *
     *****************************************************************************/

    public function setForm(IndividuRoleForm $form): void
    {
        $this->form = $form;
    }

    public function modifierAction(): ViewModel|Response
    {
        $individuRole = $this->fetchRequestedIndividuRole();
        Assert::notNull($individuRole, "IndividuRole introuvable");

        $redirect = $this->params()->fromQuery('redirect');

        $this->form->setAttribute('action', $this->url()->fromRoute('individu-role/modifier', [], ['query' => ['redirect' => $redirect]], true));
        $this->form->bind($individuRole);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->form->setData($data);
            if ($this->form->isValid()) {
                try {
                    $this->individuRoleService->save($individuRole);
                    $this->flashMessenger()->addSuccessMessage("Modification enregistrée avec succès.");
                } catch (Exception $e) {
                    $this->flashMessenger()->addErrorMessage("Impossible d'enregistrer la modification. " . $e->getMessage());
                }
                if ($redirect && !$request->isXmlHttpRequest()) {
                    return $this->redirect()->toUrl($redirect);
                }
            }
        }

        $viewModel = new ViewModel([
            'form' => $this->form,
        ]);
        $viewModel->setTemplate('individu/individu-role/modifier');

        return $viewModel;
    }

    private function fetchRequestedIndividuRole(): ?IndividuRole
    {
        $qb = $this->individuRoleService->getRepository()->createQueryBuilder('ir')
            ->addSelect('ire')->leftJoin('ir.individuRoleEtablissement', 'ire')
            ->addSelect('i')->join('ir.individu', 'i', Join::WITH, 'i = :individu')->setParameter('individu', $this->params('individu'))
            ->addSelect('r')->join('ir.role', 'r', Join::WITH, 'r = :role')->setParameter('role', $this->params('role'));

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new InvalidArgumentException("Anomalie rencontrée : IndividuRole en doublon !", null, $e);
        }
    }
}