<?php

namespace Soutenance\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use BjyAuthorize\Exception\UnAuthorizedException;
use DateInterval;
use DateTime;
use Exception;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Form\SoutenanceRefus\SoutenanceRefusForm;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Proposition\PropositionServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class SoutenanceController
 * @package Soutenance\Controller
 *
 * Controlleur principale du module de gestion de la soutenance
 */

class SoutenanceController extends AbstractActionController {
    use TheseServiceAwareTrait;
    use PropositionServiceAwareTrait;
    use MembreServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * Modification de la date et lieu de la soutenance
     * => se fait dans une fenêtre modale
     */
    public function modifierDateLieuAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceDateLieuForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/constituer/modifier-date-lieu', ['these' => $idThese], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
                $this->unvalidate($these);
            }
        }

        return new ViewModel([
                'form' => $form,
                'title' => 'Renseigner la date et le lieu de la soutenance',
            ]
        );
    }

    public function modifierMembreAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);


        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceMembreForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/constituer/modifier-membre', ['these' => $these->getId()], [], true));

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = null;
        if ($idMembre) $membre = $this->getMembreService()->find($idMembre);
        else           {
            $membre = new Membre();
            $membre->setProposition($proposition);
        }
        $form->bind($membre);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($idMembre)  {
                    $this->getMembreService()->update($membre);
                }
                else {
                    $this->getMembreService()->create($membre);
                }
                $this->unvalidate($these);
            }
        }

               return new ViewModel([
                'form' => $form,
                'title' => 'Renseigner les informations sur un membre du jury',
            ]
        );
    }

    public function effacerMembreAction() {

        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Membre $membre */
        $idMembre = $this->params()->fromRoute('membre');
        $membre = $this->getMembreService()->find($idMembre);

        if ($membre) {
            $this->getMembreService()->delete($membre);
            $this->unvalidate($these);
        }
        $this->redirect()->toRoute('soutenance/constituer',['these' => $idThese],[],true);
    }

    public function constituerAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);
        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        if (!$proposition) {
            $proposition = new Proposition();
            $proposition->setThese($these);
            $this->getPropositionService()->create($proposition);
        }

        /** @var Utilisateur $currentUser */
        $currentUser = $this->userContextService->getDbUser();
        $currentIndividu = $currentUser->getIndividu();

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();
        /** @var Individu[] $directeurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $directeurs = [];
        /** @var Acteur $acteur */
        foreach ($acteurs as $acteur) $directeurs[] = $acteur->getIndividu();

        $validations = [];
        $validations[$doctorant->getIndividu()->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $doctorant->getIndividu());
        foreach ($directeurs as $directeur) {
            $validations[$directeur->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $directeur);
        }
        return new ViewModel([
                'these' => $these,
                'proposition' => $proposition,
                'doctorant' => $doctorant,
                'directeurs' => $directeurs,
                'validations' => $validations,
                'currentIndividu' => $currentIndividu,
            ]
        );
    }

    public function validerAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        $validation = $this->getValidationService()->validatePropositionSoutenance($these);
        $this->getNotifierService()->triggerValidationProposition($these, $validation);

        /** @var Doctorant $doctorant */
        $doctorant = $these->getDoctorant();
        /** @var Individu[] $directeurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $acteurs = array_merge($dirs->toArray(), $codirs->toArray());
        $acteurs[] = $doctorant;

        $allValidated = true;
        foreach ($acteurs as $acteur) {
            if ($this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $acteur->getIndividu()) === null) {
                $allValidated = false;
                break;
            }
        }

        if ($allValidated) $this->getNotifierService()->triggerNotificationUniteRechercheProposition($these);



        $this->redirect()->toRoute('soutenance/constituer',['these' => $idThese],[],true);

    }

    /**
     * @param These $these
     */
    public function unvalidate($these) {
        /** @var Validation[] $validations */
        $validations = $this->getValidationService()->findValidationPropositionSoutenanceByThese($these);
        foreach ($validations as $validation) {
            $this->getValidationService()->historise($validation);
            $this->getNotifierService()->triggerDevalidationProposition($validation);
        }
    }

    public function validerUrAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var Validation[] $validations*/
        $validations = [];

        $doctorant = $these->getDoctorant()->getIndividu();
        $validations[$doctorant->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $doctorant);
        /** @var Acteur[] $directeurs */
        $dirs = $these->getActeursByRoleCode(Role::CODE_DIRECTEUR_THESE);
        $codirs = $these->getActeursByRoleCode(Role::CODE_CODIRECTEUR_THESE);
        $directeurs = array_merge($dirs->toArray(), $codirs->toArray());
        foreach ($directeurs as $directeur) {
            $validations[$directeur->getIndividu()->getId()] = $this->getValidationService()->findValidationPropositionSoutenanceByTheseAndIndividu($these, $directeur->getIndividu());
        }


        return new ViewModel([
            'proposition' => $proposition,
            'validation' => $validations,
        ]);
    }

    public function validerUrRefusAction() {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var SoutenanceRefusForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceRefusForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/valider-ur/refuser', ['these' => $these->getId()], [], true));

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            if ($data['motif'] !== null) {
                $this->unvalidate($these);
                $currentUser = $this->userContextService->getIdentityIndividu();
                $this->getNotifierService()->triggerRefusPropositionSoutenance($these, $currentUser, $data['motif']);
//                $this->redirect()->toRoute('soutenance/constituer',['these' => $these->getId()],[],true);
            }
        }

        return new ViewModel([
                'form' => $form,
                'these' => $these,
            ]
        );
    }

    /**
     * Cette fonction est dédiée aux opérations réalisées par la maison des doctorats post-validation :
     * - adaptation des date de rendu des rapports ;
     * - Rensignement des persopass des membres du jury
     * - Envoie des demandes d'expertise
     * @return ViewModel
     */
    public function presoutenanceAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);
        $rapporteurs = $this->getPropositionService()->getRapporteurs($proposition);

        /** Si la proposition ne possède pas encore de date de rendu de rapport alors la valeur par défaut est donnée */
        $renduRapport = $proposition->getRenduRapport();
        if (!$renduRapport) {
            try {
                $renduRapport = $proposition->getDate();
                $renduRapport = $renduRapport->sub(new DateInterval('P21D'));
            } catch (Exception $e) {
                throw new RuntimeException("Un problème a été rencontré lors du calcul de la date de rendu des rapport.");
            }
            $proposition->setRenduRapport($renduRapport);
            $this->getPropositionService()->update($proposition);
        }

        return new ViewModel([
            'these' => $these,
            'proposition' => $proposition,
            'rapporteurs' => $rapporteurs,
        ]);
    }

    public function dateRenduRapportAction()
    {
        /** @var These $these */
        $idThese = $this->params()->fromRoute('these');
        $these = $this->getTheseService()->getRepository()->find($idThese);

        /** @var Proposition $proposition */
        $proposition = $this->getPropositionService()->findByThese($these);

        /** @var SoutenanceDateLieuForm $form */
        $form = $this->getServiceLocator()->get('FormElementManager')->get(SoutenanceDateRenduRapportForm::class);
        $form->setAttribute('action', $this->url()->fromRoute('soutenance/presoutenance/date-rendu-rapport', ['these' => $these->getId()], [], true));
        $form->bind($proposition);

        /** @var Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->getPropositionService()->update($proposition);
            }
        }

        return new ViewModel([
                'form' => $form,
                'title' => 'Modification de la date de rendu des rapports',
            ]
        );
    }
}

