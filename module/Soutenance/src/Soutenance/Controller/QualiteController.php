<?php

namespace Soutenance\Controller;

use Soutenance\Entity\Qualite;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class QualiteController extends AbstractActionController
{
    use MembreServiceAwareTrait;

    /**
     * Affiche la liste des qualités enregistrées dans SyGAL et permet l'accés aux fonctions d'ajout, d'édition et de retrait
     */
    public function indexAction() {
        $qualites = $this->getMembreService()->findAllQualites();

        return new ViewModel([
            'qualites' => $qualites,
        ]);
    }

    public function editerAction() {
        /** @var Qualite $qualite */
        $idQualite = $this->params()->fromRoute('qualite');
        $qualite = null;
        if ($idQualite) {
            $qualite = $this->getMembreService()->getQualiteById($idQualite);
        } else {
            $qualite = new Qualite();
        }

        /** @var QualiteEditionForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(QualiteEditionForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/qualite/editer', ['qualite' => $idQualite], [], true));
        $form->bind($qualite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($idQualite) {
                    $this->getMembreService()->update($qualite);
                } else {
                    $this->getMembreService()->createQualite($qualite);
                }
            }
        }

        return  new ViewModel([
            'form' => $form,
        ]);
    }

    public function effacerAction() {
        /** @var Qualite $qualite */
        $idQualite = $this->params()->fromRoute('qualite');
        $qualite = $this->getMembreService()->getQualiteById($idQualite);

        $this->getMembreService()->removeQualite($qualite);

        $this->redirect()->toRoute('soutenance/qualite', [], [], true);
    }

}