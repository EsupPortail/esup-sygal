<?php

namespace Soutenance\Controller\Qualite;

use BjyAuthorize\Exception\UnAuthorizedException;
use Soutenance\Entity\Qualite;
use Soutenance\Entity\QualiteLibelleSupplementaire;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteEdition\QualiteEditionFormAwareTrait;
use Soutenance\Form\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireFormAwareTrait;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Service\Qualite\QualiteServiceAwareTrait;
use Soutenance\Service\QualiteLibelleSupplementaire\QualiteLibelleSupplementaireServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @method boolean isAllowed($resource, $privilege = null)
 */

class QualiteController extends AbstractActionController
{
    use QualiteServiceAwareTrait;
    use QualiteLibelleSupplementaireServiceAwareTrait;

    use QualiteEditionFormAwareTrait;
    use QualiteLibelleSupplementaireFormAwareTrait;

    /**
     * Affiche la liste des qualités enregistrées dans SyGAL et permet l'accés aux fonctions d'ajout, d'édition et de retrait
     */
    public function indexAction() {

        $isAllowed = $this->isAllowed(QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER));
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à visualiser la liste des qualités affectables aux membres du jury.");
        }

        $qualites = $this->getQualiteService()->findAllQualites();

        return new ViewModel([
            'qualites' => $qualites,
        ]);
    }

    public function editerAction() {

        $isAllowed = $this->isAllowed(QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER));
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier la liste des qualités affectables aux membres du jury.");
        }

        /** @var Qualite $qualite */
        $idQualite = $this->params()->fromRoute('qualite');
        $qualite = null;
        if ($idQualite !== null) {
            $qualite = $this->getQualiteService()->getQualiteById($idQualite);
        } else {
            $qualite = new Qualite();
        }

        /** @var QualiteEditionForm $form */
        $form = $this->getQualiteEditionForm();
        $form->setAttribute('action', $this->url()->fromRoute('qualite/editer', ['qualite' => $idQualite], [], true));
        $form->bind($qualite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($idQualite) {
                    $this->getQualiteService()->updateQualite($qualite);
                } else {
                    $this->getQualiteService()->createQualite($qualite);
                }
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => 'Édition d\'une qualité',
            'form' => $form,
        ]);
        return  $vm;
    }

    public function effacerAction() {

        $isAllowed = $this->isAllowed(QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER));
        if (!$isAllowed) {
            throw new UnAuthorizedException("Vous êtes non authorisé(e) à modifier la liste des qualités affectables aux membres du jury.");
        }

        /** @var Qualite $qualite */
        $idQualite = $this->params()->fromRoute('qualite');
        $qualite = $this->getQualiteService()->getQualiteById($idQualite);

        $this->getQualiteService()->removeQualite($qualite);

        $this->redirect()->toRoute('qualite', [], [], true);
    }

    public function ajouterLibelleSupplementaireAction()
    {
        $qualite = $this->getQualiteService()->getRequestedQualite($this);
        $libelleSupplementaire = new QualiteLibelleSupplementaire();
        $libelleSupplementaire->setQualite($qualite);

        $form = $this->getQualiteLibelleSupplementaireForm();
        $form->setAttribute('action', $this->url()->fromRoute('qualite/ajouter-libelle-supplementaire', ['qualite' => $qualite->getId()], [], true));
        $form->bind($libelleSupplementaire);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getQualiteLibelleSupplementaireService()->create($libelleSupplementaire);
                exit();
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('soutenance/default/default-form');
        $vm->setVariables([
            'title' => "Ajout d'un libellé pour [".$qualite->getLibelle()."]",
            'form' => $form,
        ]);
        return $vm;
    }

    public function retirerLibelleSupplementaireAction()
    {
        $libelle = $this->getQualiteLibelleSupplementaireService()->getRequestedLibelle($this);
        $this->getQualiteLibelleSupplementaireService()->delete($libelle);
        return $this->redirect()->toRoute('qualite');
    }

}