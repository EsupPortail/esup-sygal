<?php

namespace Depot\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\TypeValidation;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Depot\Process\Validation\DepotValidationProcessAwareTrait;
use Depot\Provider\Privilege\ValidationPrivileges;
use Depot\Service\Notification\DepotNotificationFactoryAwareTrait;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Exception;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Notification\Service\NotifierServiceAwareTrait;
use These\Entity\Db\Acteur;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class ValidationController extends AbstractController
{
    use TheseServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use DepotNotificationFactoryAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use DepotServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use DepotValidationProcessAwareTrait;

    public function pageDeCouvertureAction(): ViewModel
    {
        // todo : utiliser un process pour remonter les erreurs proprement, cf. modifierValidationCorrectionTheseAction()

        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            if ($action === 'valider') {
                $this->depotValidationService->validatePageDeCouverture($these);
                $successMessage = "Validation de la page de couverture enregistrée avec succès.";

                // notification
                $notification = $this->depotNotificationFactory->createNotificationValidationPageDeCouverture($these, $action);
                $this->notifierService->trigger($notification);
            }
            elseif ($action === 'devalider') {
                $this->depotValidationService->unvalidatePageDeCouverture($these);
                $successMessage ="Validation de la page de couverture annulée avec succès.";
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $this->flashMessenger()->addMessage($successMessage, 'PageDeCouverture/success');
        }

        // récupération du modèle de vue auprès du plugin et passage de variables classique
        $viewModel = $this->confirm()->getViewModel();

        $viewModel->setVariables([
            'title'  => "Validation de la page de couverture",
            'these'  => $these,
            'action' => $action,
        ]);

        return $viewModel;
    }

    public function validationPageDeCouvertureAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these' => $these,
        ]);

        $view->setTemplate('depot/validation/page-de-couverture');

        return $view;
    }

    public function rdvBuAction()
    {
        // todo : utiliser un process pour remonter les erreurs proprement, cf. modifierValidationCorrectionTheseAction()

        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $notification = $this->depotNotificationFactory->createNotificationValidationRdvBu($these);

            if ($action === 'valider') {
                $this->depotValidationService->validateRdvBu($these, $this->userContextService->getIdentityIndividu());
                $successMessage = "Validation enregistrée avec succès.";

                // notification (doctorant: à la 1ere validation seulement)
                $notifierDoctorant = ! $this->depotValidationService->existsValidationRdvBuHistorisee($these);
                $notification->setNotifierDoctorant($notifierDoctorant);
            }
            elseif ($action === 'devalider') {
                $this->depotValidationService->unvalidateRdvBu($these);
                $successMessage ="Validation annulée avec succès.";

                // notification
                $notification->setEstDevalidation(true);
                $notification->setNotifierDoctorant(false);
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $this->notifierService->trigger($notification);

            $this->flashMessenger()->addSuccessMessage($successMessage);
        }

        // récupération du modèle de vue auprès du plugin et passage de variables classique
        $viewModel = $this->confirm()->getViewModel();

        $viewModel->setVariables([
            'title'  => "Validation à l'issue du rendez-vous avec la bibliothèque universitaire",
            'these'  => $these,
            'action' => $action,
        ]);

        return $viewModel;
    }

    public function validationDepotTheseCorrigeeAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these'          => $these,
            'validerUrl'     => $this->urlDepot()->validerDepotTheseCorrigeeUrl($these),
            'devaliderUrl'   => $this->urlDepot()->devaliderDepotTheseCorrigeeUrl($these),
            'typeValidation' => $this->validationService->findTypeValidationByCode($type = TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
            'validation'     => $these->getValidation($type),
        ]);

        $view->setTemplate('depot/validation/these-corrigee/validation-depot');

        return $view;
    }

    public function validationCorrectionTheseAction()
    {
        $these = $this->requestedThese();

        $utilisateurs = [];
        /** @var Acteur $acteur */
        foreach ($these->getActeurs() as $acteur) {
            $individu = $acteur->getIndividu();
            $utilisateurs[$individu->getId()] = $this->utilisateurService->getRepository()->findByIndividu($individu); // ok
        }

        $view = new ViewModel([
            'these'          => $these,
            'validerUrl'     => $this->urlDepot()->validerCorrectionTheseUrl($these),
            'devaliderUrl'   => $this->urlDepot()->devaliderCorrectionTheseUrl($these),
            'typeValidation' => $this->validationService->findTypeValidationByCode($type = TypeValidation::CODE_CORRECTION_THESE),
            'validations'    => $these->getValidations($type),
            'attendues'      => $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($these),
            'utilisateurs'   => $utilisateurs,
        ]);

        $view->setTemplate('depot/validation/these-corrigee/validation-correction');

        return $view;
    }

    public function modifierValidationDepotTheseCorrigeeAction(): ViewModel
    {
        // todo : utiliser un process pour remonter les erreurs proprement, cf. modifierValidationCorrectionTheseAction()

        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            if ($action === 'valider') {
                $validation = $this->depotValidationService->validateDepotTheseCorrigee($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification des directeurs de thèse
                $resultArray = $this->depotService->notifierCorrectionsApportees($these);
            }
            elseif ($action === 'devalider') {
                $validation = $this->depotValidationService->unvalidateDepotTheseCorrigee($these);
                $successMessage ="Validation annulée avec succès.";

                // pas de notification par mail
                $resultArray = null;
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $tvCode = $validation->getTypeValidation()->getCode();
            $this->flashMessenger()->addMessage($successMessage, "$tvCode/success");
            if ($resultArray) {
                if ($resultArray[0] === 'success') {
                    $this->flashMessenger()->addMessage($resultArray[1], "$tvCode/info");
                }
                if ($resultArray[0] === 'error') {
                    $this->flashMessenger()->addMessage($resultArray[1], "$tvCode/danger");
                }
            }
        }

        // récupération du modèle de vue auprès du plugin et passage de variables classique
        $viewModel = $this->confirm()->getViewModel();

        $viewModel->setVariables([
            'title'  => "Validation du dépôt de la thèse corrigée",
            'these'  => $these,
            'action' => $action,
        ]);

        $viewModel->setTemplate('depot/validation/these-corrigee/modifier-validation-depot');

        return $viewModel;
    }

    public function modifierValidationCorrectionTheseAction(): Response
    {
        $these = $this->requestedThese();
        $action = $this->params()->fromQuery('action');

        try {
            if ($action === 'valider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE);
                $validation = $this->depotValidationProcess->validateCorrectionThese($these);
                $successMessage = "Validation enregistrée avec succès.";
            } elseif ($action === 'devalider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR);
                $validation = $this->depotValidationProcess->unvalidateCorrectionThese($these);
                $successMessage = "Validation annulée avec succès.";
            } else {
                throw new RuntimeException("Action inattendue!");
            }
            $this->flashMessenger()->addSuccessMessage($successMessage);
        } catch (Exception $e) {
            $this->flashMessenger()->addErrorMessage("Validation impossible. " . $e->getMessage());
        }


        $notificationLogs = $this->depotValidationProcess->getNotificationLogs();
        if (isset($notificationLogs['info'])) {
            $this->flashMessenger()->addInfoMessage($notificationLogs['success']);
        }
        if (isset($notificationLogs['danger'])) {
            $this->flashMessenger()->addErrorMessage($notificationLogs['danger']);
        }

        return $this->redirect()->toUrl($this->urlDepot()->validationTheseCorrigeeUrl($these));
    }
}