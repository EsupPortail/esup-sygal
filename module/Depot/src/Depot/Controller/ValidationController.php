<?php

namespace Depot\Controller;

use Application\Controller\AbstractController;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Depot\Notification\ValidationRdvBuNotification;
use Depot\Provider\Privilege\ValidationPrivileges;
use Depot\Service\These\DepotServiceAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Laminas\View\Model\ViewModel;
use Notification\Notification;
use These\Entity\Db\Acteur;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

class ValidationController extends AbstractController
{
    use TheseServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use DepotServiceAwareTrait;
    use ValidationServiceAwareTrait;

    public function pageDeCouvertureAction(): ViewModel
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            if ($action === 'valider') {
                $this->depotValidationService->validatePageDeCouverture($these);
                $successMessage = "Validation de la page de couverture enregistrée avec succès.";

                // notification
                $this->notifierService->triggerValidationPageDeCouvertureNotification($these, $action);
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
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $notification = new ValidationRdvBuNotification();
            $notification->setThese($these);

            if ($action === 'valider') {
                $this->depotValidationService->validateRdvBu($these, $this->userContextService->getIdentityIndividu());
                $successMessage = "Validation enregistrée avec succès.";

                // notification (doctorant: à la 1ere validation seulement)
                $notifierDoctorant = ! $this->depotValidationService->existsValidationRdvBuHistorisee($these);
                $notification->setNotifierDoctorant($notifierDoctorant);
                $this->notifierService->triggerValidationRdvBu($notification);
            }
            elseif ($action === 'devalider') {
                $this->depotValidationService->unvalidateRdvBu($these);
                $successMessage ="Validation annulée avec succès.";

                // notification
                $notification->setEstDevalidation(true);
                $notification->setNotifierDoctorant(false);
                $this->notifierService->triggerValidationRdvBu($notification);
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

//            $notificationLog = $this->notifierService->getMessage('<br>', 'info');

            $this->flashMessenger()->addSuccessMessage($successMessage);
//            $this->flashMessenger()->addInfoMessage($notificationLog);
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

    /**
     * @throws \Notification\Exception\NotificationImpossibleException
     */
    public function modifierValidationDepotTheseCorrigeeAction(): ViewModel
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            if ($action === 'valider') {
                $validation = $this->depotValidationService->validateDepotTheseCorrigee($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification des directeurs de thèse
                $this->depotService->notifierCorrectionsApportees($these);
            }
            elseif ($action === 'devalider') {
                $validation = $this->depotValidationService->unvalidateDepotTheseCorrigee($these);
                $successMessage ="Validation annulée avec succès.";

                // pas de notification par mail
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $notificationLogs = $this->notifierService->getLogs();

            $tvCode = $validation->getTypeValidation()->getCode();
            $this->flashMessenger()->addMessage($successMessage, "$tvCode/success");
            if (isset($notificationLogs['info'])) {
                $this->flashMessenger()->addMessage($notificationLogs['info'], "$tvCode/info");
            }
            if (isset($notificationLogs['danger'])) {
                $this->flashMessenger()->addMessage($notificationLogs['danger'], "$tvCode/danger");
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

    public function modifierValidationCorrectionTheseAction()
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            if ($action === 'valider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE);

                $validation = $this->depotValidationService->validateCorrectionThese($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification par mail si plus aucune validation attendue
                $results = $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($these);
                if (count($results) === 0) {
                    $notif = new Notification();
                    $notif
                        ->setSubject("Validation des corrections de la thèse")
                        ->setTemplatePath('application/notification/mail/notif-validation-correction-these')
                        ->setTemplateVariables([
                            'these' => $these,
                            'role' => $this->roleService->getRepository()->findOneBy(['code' => Role::CODE_PRESIDENT_JURY]),
                            'url' => $this->url()->fromRoute('these/depot', ['these' => $these->getId()], ['force_canonical' => true]),
                        ]);
                    // notification du BDD
                    $this->notifierService->triggerValidationCorrectionThese($notif, $these);
                    // notification du doctorant
                    $this->notifierService->triggerValidationCorrectionTheseEtudiant($notif, $these);

                    //todo mail pour etudiant
                }
            }
            elseif ($action === 'devalider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR);

                $validation = $this->depotValidationService->unvalidateCorrectionThese($these);
                $successMessage ="Validation annulée avec succès.";

                // pas de notification par mail
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $notificationLogs = $this->notifierService->getLogs();

            $tvCode = $validation->getTypeValidation()->getCode();
            $this->flashMessenger()->addMessage($successMessage, "$tvCode/success");
            if (isset($notificationLogs['info'])) {
                $this->flashMessenger()->addMessage($notificationLogs['info'], "$tvCode/info");
            }
            if (isset($notificationLogs['danger'])) {
                $this->flashMessenger()->addMessage($notificationLogs['danger'], "$tvCode/danger");
            }
        }

        // récupération du modèle de vue auprès du plugin et passage de variables classique
        $viewModel = $this->confirm()->getViewModel();

        $viewModel->setVariables([
            'title'  => "Validation des corrections de la thèse",
            'these'  => $these,
            'action' => $action,
        ]);

        $viewModel->setTemplate('depot/validation/these-corrigee/modifier-validation-correction');

        return $viewModel;
    }
}