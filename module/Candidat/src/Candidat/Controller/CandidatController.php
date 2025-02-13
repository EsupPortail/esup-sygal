<?php

namespace Candidat\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\MailConfirmation;
use Application\Form\MailConfirmationForm;
use Application\RouteMatch;
use Application\Search\Controller\SearchControllerInterface;
use Application\Search\Controller\SearchControllerTrait;
use Application\Search\SearchServiceAwareTrait;
use Application\Service\MailConfirmationService;
use Candidat\Entity\Db\Candidat;
use Candidat\Service\CandidatServiceAwareTrait;
use Doctorant\Form\MailConsentementForm;
use Laminas\Http\Response;
use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class CandidatController extends AbstractController implements SearchControllerInterface
{
    use SearchServiceAwareTrait;
    use SearchControllerTrait;

    use CandidatServiceAwareTrait;

    /** @var MailConfirmationService $mailConfirmationService */
    private $mailConfirmationService;

    private MailConfirmationForm $mailConfirmationForm;
    private MailConsentementForm $mailConsentementForm;


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
        $candidat = $this->candidatService->getRepository()->find($this->params('candidat'));
        if ($candidat === null) {
            throw new \InvalidArgumentException("Individu introuvable.");
        }

        return new ViewModel([
            'candidat' => $candidat,
        ]);
    }

    public function consulterAction(): ViewModel
    {
        /** @var Candidat $candidat */
        $candidat = $this->candidatService->getRepository()->find($this->params('candidat'));

        return new ViewModel([
            'candidat' => $candidat,
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function modifierEmailContactAction()
    {
        $candidat = $this->requestCandidat();
        $force = (bool) $this->params()->fromQuery('force', false);

        $lastMailConfirmation = $this->mailConfirmationService->fetchMailConfirmationForIndividu($candidat->getIndividu());

        if (!$force && $lastMailConfirmation) {
            // Si on a déjà une demande en attente...
            if ($lastMailConfirmation->estEnvoye()) {
                return $this->changementEmailContactEnCours($candidat, $lastMailConfirmation);
            }
            // Si on a déjà une demande confirmée et que le consentement a été fourni...
            if ($lastMailConfirmation->estConfirme()) {
                return $this->changementEmailContactConfirme($lastMailConfirmation);
            }
        }

        $mailConfirmation = new MailConfirmation();
        $mailConfirmation->setIndividu($candidat->getIndividu());
        // inti avec précédent mail renseigné
        if ($lastMailConfirmation) {
            $mailConfirmation->setEmail($lastMailConfirmation->getEmail());
            $mailConfirmation->setRefusListeDiff($lastMailConfirmation->getRefusListeDiff());
        }

        $this->mailConfirmationForm->bind($mailConfirmation);

        $emailInstitutionnel = $candidat->getIndividu()->getEmailPro();
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
            $this->urlCandidat()->modifierEmailContactUrl($candidat, $force));

        return new ViewModel([
            'candidat' => $candidat,
            'emailInstitutionnel' => $emailInstitutionnel,
            'form' => $this->mailConfirmationForm,
            'title' => "Adresse électronique de contact et consentement",
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function modifierEmailContactConsentAction()
    {
        $candidat = $this->requestCandidat();
        $redirect = $this->params()->fromQuery('redirect', '/');

        $lastMailConfirmation = $this->mailConfirmationService->fetchMailConfirmationForIndividu($candidat->getIndividu());
        if ($lastMailConfirmation === null || !$lastMailConfirmation->estConfirme()) {
            return $this->modifierEmailContactAction();
        }

        $this->mailConsentementForm->bind($lastMailConfirmation);

        $emailInstitutionnel = $candidat->getIndividu()->getEmailPro();
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
            'candidat' => $candidat,
            'emailInstitutionnel' => $emailInstitutionnel,
            'form' => $this->mailConsentementForm,
            'title' => "Consentement",
        ]);
    }

    /**
     * @param MailConfirmation $mailConfirmation
     * @return ViewModel
     */
    public function changementEmailContactConfirme(MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'email' => $mailConfirmation->getEmail(),
        ]);

        $viewmodel->setTemplate('candidat/candidat/demande-ok');

        return $viewmodel;
    }

    /**
     * @param Candidat $candidat
     * @param MailConfirmation $mailConfirmation
     * @return ViewModel
     */
    public function changementEmailContactEnCours(Candidat $candidat, MailConfirmation $mailConfirmation): ViewModel
    {
        $viewmodel = new ViewModel([
            'candidat' => $candidat,
            'email' => $mailConfirmation->getEmail(),
        ]);
        $viewmodel->setTemplate('candidat/candidat/demande-encours');

        return $viewmodel;
    }

    /**
     * @return Candidat|null
     */
    private function requestCandidat(): ?Candidat
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $this->getEvent()->getRouteMatch();

        return $routeMatch->getCandidat();
    }

    public function rechercherAction() : JsonModel
    {
        if (($term = $this->params()->fromQuery('term'))) {
            $doctorants = $this->candidatService->getCandidatsByTerm($term);
            $result = [];
            foreach ($doctorants as $candidat) {
                $result[] = array(
                    'id'    => $candidat->getId(),
                    'label' => $candidat->getIndividu()->getPrenom(). " " . (($candidat->getIndividu()->getNomUsuel())??$candidat->getIndividu()->getNomPatronymique()),
                    'extra' => "<span class='badge' style='background-color: slategray;'>".$candidat->getIne()."</span>",
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
