<?php

namespace ComiteSuivi\Controller;


use Application\Entity\Db\Individu;
use Application\Entity\Db\These;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use ComiteSuivi\Entity\DateTimeTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Entity\Db\Membre;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviFormAwareTrait;
use ComiteSuivi\Form\ComiteSuivi\RefusForm;
use ComiteSuivi\Form\ComiteSuivi\RefusFormAwareTrait;
use ComiteSuivi\Form\CompteRendu\CompteRenduFormAwareTrait;
use ComiteSuivi\Form\Membre\MembreFormAwareTrait;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceAwareTrait;
use ComiteSuivi\Service\CompteRendu\CompteRenduServiceAwareTrait;
use ComiteSuivi\Service\Membre\MembreServiceAwareTrait;
use ComiteSuivi\Service\Notifier\NotifierServiceAwareTrait;
use ComiteSuivi\View\Helper\AnneeTheseViewHelper;
use Doctrine\ORM\ORMException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ComiteSuiviController extends AbstractActionController {
    use DateTimeTrait;

    use ComiteSuiviServiceAwareTrait;
    use CompteRenduServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use UserServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use ValidationServiceAwareTrait;

    use ComiteSuiviFormAwareTrait;
    use CompteRenduFormAwareTrait;
    use MembreFormAwareTrait;
    use RefusFormAwareTrait;

    public function indexAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        $comites = [];
        if ($these !== null) {
            $comites = $this->getComiteSuiviService()->getComitesSuivisByThese($these);
        }

        return new ViewModel([
            'these' => $these,
            'comites' => $comites,
        ]);
    }

    public function ajouterAction()
    {
        /** @var These $these */
        $theseId = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($theseId);

        /** @var ComiteSuivi[] $comites */
        $comites = $this->getComiteSuiviService()->getComitesSuivisByThese($these);
        $max = 1;
        foreach ($comites as $comite) {
            if ($comite->getAnneeThese() > $max) $max = $comite->getAnneeThese();
        }

        $comite = new ComiteSuivi();
        $comite->setThese($these);
        $comite->setAnneeScolaire($this->getAnneeScolaire());
        $comite->setAnneeThese($max+1);

        $form = $this->getComiteSuiviForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/ajouter', [], [], true));
        $form->bind($comite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getComiteSuiviService()->create($comite);
                exit();
            }
        }

        $vm =  new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setTerminal(true);
        $vm->setVariables([
            'title' => "Ajout d'un nouveau comité de suivi de thèse",
            'form' => $form,
        ]);
        return $vm;
    }

    public function afficherAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $anneeViewHelper = new AnneeTheseViewHelper();
        $anneeThese = strtolower($anneeViewHelper->render($comite->getAnneeThese()));

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/comite-suivi/instance');
        $vm->setVariables([
            'title' => "Comité de suivi de thèse pour la " . $anneeThese . " de la thèse de " .$comite->getThese()->getDoctorant()->getIndividu()->getNomComplet(),
            'these' => $comite->getThese(),
            'comite' => $comite,
            'action' => 'afficher',
        ]);
        return $vm;
    }

    public function modifierAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/comite-suivi/instance');
        $vm->setVariables([
            'these' => $comite->getThese(),
            'comite' => $comite,
            'action' => 'modifier',
        ]);
        return $vm;
    }

    public function modifierInfosAction()
    {
        /** @var ComiteSuivi $comite */
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $form = $this->getComiteSuiviForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/modifier-infos', ['these' => $comite->getThese()->getId(), 'comite-suivi' => $comite->getId()], [], true));
        $form->bind($comite);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getComiteSuiviService()->update($comite);
                exit();
            }
        }

        $vm =  new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setTerminal(true);
        $vm->setVariables([
            'title' => "Modifier les informations du comité de suivi de thèse",
            'form' => $form,
        ]);
        return $vm;
    }

    public function restaurerAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $this->getComiteSuiviService()->restore($comite);
        return $this->redirect()->toRoute('comite-suivi', ['these' => $comite->getThese()->getId()], [], true);
    }

    public function historiserAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $this->getComiteSuiviService()->historise($comite);
        return $this->redirect()->toRoute('comite-suivi', ['these' => $comite->getThese()->getId()], [], true);
    }

    public function supprimerAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data["reponse"] === "oui") $this->getComiteSuiviService()->delete($comite);
            exit();
        }

        $vm = new ViewModel();
        if ($comite !== null) {
            $vm->setTemplate('comite-suivi/default/confirmation');
            $vm->setVariables([
                'title' => "Suppression de l'instance du comité de suivi de thèse",
                'text' => "La suppression est définitive êtes-vous sûr&middot;e de vouloir continuer ?",
                'action' => $this->url()->fromRoute('comite-suivi/supprimer', ["comite-suivi" => $comite->getId()], [], true),
            ]);
        }
        return $vm;
    }

    public function finaliserAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $validation = $this->validationService->finaliseComiteSuivi($comite);
        $this->getNotifierService()->triggerFinalisation($comite);

        $comite->setFinalisation($validation);
        $this->getComiteSuiviService()->update($comite);

        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $comite->getThese()->getId(), 'comite' => $comite->getId()], [], true);
    }

    public function validerAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);
        $validation = $this->validationService->validateComiteSuivi($comite);
        $this->getNotifierService()->triggerValidation($comite);

        $comite->setValidation($validation);
        $this->getComiteSuiviService()->update($comite);

        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $comite->getThese()->getId(), 'comite' => $comite->getId()], [], true);
    }

    public function refuserAction() {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        /** @var RefusForm $form */
        $form = $this->getRefusForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/refuser', ['these' => $comite->getThese()->getId(), 'comite' => $comite->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null AND $data['motif'] !== '') {
                $this->validationService->historiserValidation($comite->getFinalisation());
                $comite->setFinalisation(null);
                $this->getComiteSuiviService()->update($comite);
                $this->getNotifierService()->triggerRefus($comite, $data['motif']);
            }
        }

        return new ViewModel([
            'title'             => "Motivation du refus du comité de suivi de thèse",
            'form'              => $form,
            'comite'            => $comite,
        ]);
    }

    /** PARTIE MEMBRE *************************************************************************************************/

    public function ajouterMembreAction()
    {
        $comite = $this->getComiteSuiviService()->getRequestedComiteSuivi($this);

        $membre = new Membre();
        $membre->setComite($comite);

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/ajouter-membre', ['comite-suivi' => $comite->getId()], [], true));
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMembreService()->create($membre);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setVariables([
            'title' => "Ajout d'un membre au comité de suivi",
            'form' => $form,
        ]);
        return $vm;
    }

    public function modifierMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);

        $form = $this->getMembreForm();
        $form->setAttribute('action', $this->url()->fromRoute('comite-suivi/modifier-membre', ['membre' => $membre->getId()], [], true));
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getMembreService()->update($membre);
            }
        }

        $vm = new ViewModel();
        $vm->setTemplate('comite-suivi/default/default-form');
        $vm->setVariables([
            'title' => "Modification d'un membre au comité de suivi",
            'form' => $form,
        ]);
        return $vm;
    }

    public function historiserMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->historise($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }

    public function restaurerMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->restore($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }

    public function supprimerMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $this->getMembreService()->delete($membre);
        return $this->redirect()->toRoute('comite-suivi/modifier', ['these' => $membre->getComite()->getThese()->getId(), 'comite-suivi' => $membre->getComite()->getId()], [], true);
    }

    public function lierMembreAction()
    {
        $membre = $this->getMembreService()->getRequestedMembre($this);
        $comite = $membre->getComite();

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $individu = null;
            if (isset($data['type']) AND $data['type'] === 'creation') {
                $individu = $this->getMembreService()->createIndividuFromMembre($membre);
                //TODO bouger cela une fois la fusion faite
                try {
                    $this->getIndividuService()->getEntityManager()->persist($individu);
                    $this->getIndividuService()->getEntityManager()->flush($individu);
                } catch (ORMException $e) {
                    throw new RuntimeException("Un problème est survenue lors de l'enregistrement de l'individu.", 0, $e);
                }
            }
            if (isset($data['type']) AND $data['type'] === 'importation') {
                $individuId = isset($data['individu']['id'])?$data['individu']['id']:null;
                /** @var Individu $individu */
                $individu = $this->getIndividuService()->getRepository()->find($individuId);
            }
            $membre->setIndividu($individu);
            $this->getMembreService()->update($membre);
            $examinateur = $this->getRoleService()->getRepository()->findByCode(Membre::ROLE_EXAMINATEUR_CODE);
            $this->getRoleService()->addIndividuRole($individu,$examinateur);

            $this->getNotifierService()->triggerNotifierExaminateur($comite, $membre);
            $utilisateurs = $this->utilisateurService->getRepository()->findByIndividu($individu);

            if (empty($utilisateurs)) {
                $user = $this->utilisateurService->createFromIndividu($individu, $this->generateUsername($membre), 'none');
                $this->userService->updateUserPasswordResetToken($user);
                $url = $this->url()->fromRoute('utilisateur/init-compte', ['token' => $user->getPasswordResetToken()], ['force_canonical' => true], true);
                $this->getNotifierService()->triggerInitialisationCompte($comite, $user, $url);
            }
            exit();
        }

        return new ViewModel([
            'title' => "Lien entre membre du comité et individu SyGAL",
            'comite' => $comite,
            'membre' => $membre,
        ]);
    }

    /**
     * @param Membre $membre
     * @return string
     */
    private function generateUsername(Membre $membre)
    {
        $result = $membre->getNom() . "_CST". $membre->getId();
        return $result;
    }

}