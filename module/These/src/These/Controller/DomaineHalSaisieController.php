<?php

namespace These\Controller;

use Application\Service\DomaineHal\DomaineHalServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use These\Entity\Db\These;
use These\Form\DomaineHalSaisie\DomaineHalSaisieFormAwareTrait;
use These\Service\These\TheseServiceAwareTrait;

class DomaineHalSaisieController extends AbstractActionController
{
    use TheseServiceAwareTrait;
    use DomaineHalSaisieFormAwareTrait;
    use DomaineHalServiceAwareTrait;

    private ?PhpRenderer $renderer = null;
    public function setRenderer(PhpRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    public function saisieDomaineHalAction(): ViewModel|Response
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $form = $this->getDomaineHalSaisieForm();
        $domainesHal = $this->domaineHalService->getDomainesHalAsOptions('docId');
        $form->get('domaineHalFieldset')->setDomainesHal($domainesHal);
        $form->setAttribute('action', $this->url()->fromRoute('these/saisie-domaine-hal', ['these' => $these->getId()], [], true));

        $form->bind($these);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost();
            //Permet de gérer le cas où aucune sélection n'est effectuée (afin de passer dans l'hydrateur)
            if (!isset($data['domaineHalFieldset'])) {
                $data['domaineHalFieldset'] = array("domaineHal" => array(""));
            }
            $form->setData($data);

            if ($form->isValid()) {
                if ($theseId === null) {
                    $this->getTheseService()->create($these);
                } else {
                    $this->getTheseService()->update($these);
                }
//                return $this->redirect()->toRoute('these/identite', ['these' => $these->getId()], [], true);
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('these/domaine-hal/saisie');
        $viewModel->setVariable('title', "Saisie des domaines HAL pour la thèse de " . $these->getDoctorant()->getIndividu()->getPrenom() . " " . $these->getDoctorant()->getIndividu()->getNomUsuel());
        $viewModel->setVariable('form', $form);

        return $viewModel;
    }
}