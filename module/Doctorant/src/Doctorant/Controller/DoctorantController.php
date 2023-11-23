<?php

namespace Doctorant\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\RouteMatch;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Doctorant\Entity\Db\Doctorant;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use UnicaenAuth\Authentication\Adapter\Ldap as LdapAuthAdapter;

/**
 * @property \Doctorant\Service\Search\DoctorantSearchService $searchService
 */
class DoctorantController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use DoctorantServiceAwareTrait;

    /** @var MailConfirmationService $mailConfirmationService */
    private $mailConfirmationService;

    private MailConfirmationForm $mailConfirmationForm;
    private MailConsentementForm $mailConsentementForm;

    /**
     * @var LdapAuthAdapter
     */
    private $ldapAuthAdapter;

    /**
     * @param MailConfirmationService $mailConfirmationService
     */
    public function setMailConfirmationService(MailConfirmationService $mailConfirmationService)
    {
        $this->mailConfirmationService = $mailConfirmationService;
    }

    /**
     * @param MailConfirmationForm $mailConfirmationForm
     */
    public function setMailConfirmationForm(MailConfirmationForm $mailConfirmationForm)
    {
        $this->mailConfirmationForm = $mailConfirmationForm;
    }

    public function setMailConsentementForm(MailConsentementForm $mailConsentementForm)
    {
        $this->mailConsentementForm = $mailConsentementForm;
    }

    public function indexAction(): Response|ViewModel
    {
        $result = $this->search();
        if ($result instanceof Response) {
            return $result;
        }
        /** @var LaminasPaginator $paginator */
        $paginator = $result;

        return new ViewModel([
            'paginator' => $paginator,
            'filters' => $this->filters(),
        ]);
    }

    public function voirAction(): ViewModel
    {
        $doctorant = $this->doctorantService->getRepository()->find($this->params('doctorant'));
        if ($doctorant === null) {
            throw new \InvalidArgumentException("Individu introuvable.");
        }

        return new ViewModel([
            'doctorant' => $doctorant,
        ]);
    }

    public function consulterAction(): ViewModel
    {
        /** @var \Doctorant\Entity\Db\Doctorant $doctorant */
        $doctorant = $this->doctorantService->getRepository()->find($this->params('doctorant'));

        return new ViewModel([
            'doctorant' => $doctorant,
        ]);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function modifierEmailContactAction()
    {
        $doctorant = $this->requestDoctorant();
        $force = (bool) $this->params()->fromQuery('force', false);

        $lastMailConfirmation = $this->mailConfirmationService->fetchMailConfirmationForIndividu($doctorant->getIndividu());

        if (!$force && $lastMailConfirmation) {
            // Si on a déjà une demande en attente...
            if ($lastMailConfirmation->estEnvoye()) {
                return $this->changementEmailContactEnCours($doctorant, $lastMailConfirmation);
            }
            // Si on a déjà une demande confirmée et que le consentement a été fourni...
            if ($lastMailConfirmation->estConfirme()) {
                return $this->changementEmailContactConfirme($lastMailConfirmation);
            }
        }

        $mailConfirmation = new MailConfirmation();
        $mailConfirmation->setIndividu($doctorant->getIndividu());
        // inti avec précédent mail renseigné
        if ($lastMailConfirmation) {
            $mailConfirmation->setEmail($lastMailConfirmation->getEmail());
            $mailConfirmation->setRefusListeDiff($lastMailConfirmation->getRefusListeDiff());
        }

        $this->mailConfirmationForm->bind($mailConfirmation);

        $emailInstitutionnel = $doctorant->getIndividu()->getEmailPro();
        if (!$emailInstitutionnel) {
            $this->mailConfirmationForm->setRefusListeDiffInterdit(true);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->mailConfirmationForm->setData($data);
            if ($this->mailConfirmationForm->isValid()) {
                $mailConfirmation->setEtat(MailConfirmation::ENVOYE);
                $id = $this->mailConfirmationService->save($mailConfirmation);
                $this->mailConfirmationService->generateCode($id);

                return $this->redirect()->toRoute('mail-confirmation/envoie', ['id' => $id], [], true);
            }
        }

        $this->mailConfirmationForm->setAttribute('action',
            $this->urlDoctorant()->modifierEmailContactUrl($doctorant, $force));

        return new ViewModel([
            'doctorant' => $doctorant,
            'emailInstitutionnel' => $emailInstitutionnel,
            'form' => $this->mailConfirmationForm,
            'title' => "Adresse électronique de contact et consentement",
        ]);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function modifierEmailContactConsentAction()
    {
        $doctorant = $this->requestDoctorant();
        $redirect = $this->params()->fromQuery('redirect', '/');

        $lastMailConfirmation = $this->mailConfirmationService->fetchMailConfirmationForIndividu($doctorant->getIndividu());
        if ($lastMailConfirmation === null || !$lastMailConfirmation->estConfirme()) {
            return $this->modifierEmailContactAction();
        }

        $this->mailConsentementForm->bind($lastMailConfirmation);

        $emailInstitutionnel = $doctorant->getIndividu()->getEmailPro();
        if (!$emailInstitutionnel) {
            // si aucun email pro trouvé : pas possible de refuser la réception sur le mail de contact
            $this->mailConsentementForm->setRefusInterdit(true);
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $this->mailConsentementForm->setData($data);
            if ($this->mailConsentementForm->isValid()) {
                $this->mailConfirmationService->save($lastMailConfirmation);

                if (!$request->isXmlHttpRequest()) {
                    return $this->redirect()->toUrl($redirect);
                }
            }
        }

        $this->mailConsentementForm->setAttribute('action', $this->url()->fromRoute(null, [], [], true));

        return new ViewModel([
            'doctorant' => $doctorant,
            'emailInstitutionnel' => $emailInstitutionnel,
            'form' => $this->mailConsentementForm,
            'title' => "Consentement",
        ]);
    }

    /**
     * @param \Application\Entity\Db\MailConfirmation $mailConfirmation
     * @return \Laminas\View\Model\ViewModel
     */
    public function changementEmailContactConfirme(MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'email' => $mailConfirmation->getEmail(),
        ]);

        $viewmodel->setTemplate('doctorant/doctorant/demande-ok');

        return $viewmodel;
    }

    /**
     * @param \Doctorant\Entity\Db\Doctorant $doctorant
     * @param \Application\Entity\Db\MailConfirmation $mailConfirmation
     * @return \Laminas\View\Model\ViewModel
     */
    public function changementEmailContactEnCours(Doctorant $doctorant, MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'doctorant' => $doctorant,
            'email' => $mailConfirmation->getEmail(),
        ]);
        $viewmodel->setTemplate('doctorant/doctorant/demande-encours');

        return $viewmodel;
    }

    /**
     * @return Doctorant|null
     */
    private function requestDoctorant(): ?Doctorant
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getDoctorant();
    }

    public function rechercherAction() : JsonModel
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $doctorants = $this->doctorantService->getDoctorantsByTerm($term);
            $result = [];
            foreach ($doctorants as $doctorant) {
                $result[] = array(
                    'id'    => $doctorant->getId(),
                    'label' => $doctorant->getIndividu()->getPrenom(). " " . (($doctorant->getIndividu()->getNomUsuel())??$doctorant->getIndividu()->getNomPatronymique()),
                    'extra' => "<span class='badge' style='background-color: slategray;'>".$doctorant->getIne()."</span>",
                );
            }
            usort($result, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            return new JsonModel($result);
        }
        exit;
    }
}
