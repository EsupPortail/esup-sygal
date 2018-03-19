<?php

namespace Application\Controller;

use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Notification\ValidationRdvBuNotification;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class ValidationController extends AbstractController

{
    use TheseServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NotificationServiceAwareTrait;
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;

    public function rdvBuAction()
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $notification = new ValidationRdvBuNotification();
            $notification->setVariableService($this->variableService);
            $notification->setThese($these);

            if ($action === 'valider') {
                $this->validationService->validateRdvBu($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification (doctorant: à la 1ere validation seulement)
                $notification->setNotifierDoctorant(! $this->validationService->existsValidationRdvBuHistorisee($these));
                $this->notificationService->trigger($notification);
            }
            elseif ($action === 'devalider') {
                $this->validationService->unvalidateRdvBu($these);
                $successMessage ="Validation annulée avec succès.";

                // notification
                $notification->setEstDevalidation(true);
                $notification->setNotifierDoctorant(false);
                $this->notificationService->trigger($notification);
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $notificationLog = $this->notificationService->getMessage('<br>', 'info');

            $this->flashMessenger()->addSuccessMessage($successMessage);
            $this->flashMessenger()->addInfoMessage($notificationLog);
        }

        // récupération du modèle de vue auprès du plugin et passage de variables classique
        $viewModel = $this->confirm()->getViewModel();

        $viewModel->setVariables([
            'title'  => "Validation à l'issue du rendez-vous BU",
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
            'validerUrl'     => $this->urlThese()->validerDepotTheseCorrigeeUrl($these),
            'devaliderUrl'   => $this->urlThese()->devaliderDepotTheseCorrigeeUrl($these),
            'typeValidation' => $this->validationService->getTypeValidation($type = TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
            'validation'     => $these->getValidation($type),
        ]);

        $view->setTemplate('application/validation/these-corrigee/validation-depot');

        return $view;
    }

    public function validationCorrectionTheseAction()
    {
        $these = $this->requestedThese();

        $view = new ViewModel([
            'these'          => $these,
            'validerUrl'     => $this->urlThese()->validerCorrectionTheseUrl($these),
            'devaliderUrl'   => $this->urlThese()->devaliderCorrectionTheseUrl($these),
            'typeValidation' => $this->validationService->getTypeValidation($type = TypeValidation::CODE_CORRECTION_THESE),
            'validations'    => $these->getValidations($type),
            'attendues'      => $this->validationService->getValidationsAttenduesPourCorrectionThese($these),
        ]);

        $view->setTemplate('application/validation/these-corrigee/validation-correction');

        return $view;
    }

    public function modifierValidationDepotTheseCorrigeeAction()
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $mailViewModel = (new ViewModel())
                ->setTemplate('application/notification/mail/notif-validation-depot-these-corrigee')
                ->setVariables([
                    'these' => $these,
                ]);
            if ($action === 'valider') {
                $validation = $this->validationService->validateDepotTheseCorrigee($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification par mail
                $mailViewModel->setVariables([
                    'subject' => "Validation du dépôt de la thèse corrigée",
                    'url' => $this->url()->fromRoute('these/validation-these-corrigee', ['these' => $these->getId()], ['force_canonical' => true]),
                ]);
                $this->notificationService->notifierValidationDepotTheseCorrigee($mailViewModel, $these);
            }
            elseif ($action === 'devalider') {
                $validation = $this->validationService->unvalidateDepotTheseCorrigee($these);
                $successMessage ="Validation annulée avec succès.";

                // pas de notification par mail
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $notificationLogs = $this->notificationService->getLogs();

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

        $viewModel->setTemplate('application/validation/these-corrigee/modifier-validation-depot');

        return $viewModel;
    }

    public function modifierValidationCorrectionTheseAction()
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $mailViewModel = (new ViewModel())
                ->setTemplate('application/notification/mail/notif-validation-correction-these')
                ->setVariables([
                    'these' => $these,
                ]);
            if ($action === 'valider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE);

                $validation = $this->validationService->validateCorrectionThese($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification par mail si plus aucune validation attendue
                $results = $this->validationService->getValidationsAttenduesPourCorrectionThese($these);
                if (count($results) === 0) {

                    //MAIL BUREAU DES DOCTORANS
                    $mailViewModel->setVariables([
                        'subject' => "Validation des corrections de la thèse",
                        'role' => $this->roleService->getRepository()->findOneBy(['code' => Role::CODE_DIRECTEUR_THESE]),
                        'url' => $this->url()->fromRoute('these/depot', ['these' => $these->getId()], ['force_canonical' => true]),
                    ]);
                    $this->notificationService->notifierValidationCorrectionThese($mailViewModel, $these);
                    $this->notificationService->notifierValidationCorrectionTheseEtudiant($mailViewModel, $these);


                    //todo mail pour etudiant
                }
            }
            elseif ($action === 'devalider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR);

                $validation = $this->validationService->unvalidateCorrectionThese($these);
                $successMessage ="Validation annulée avec succès.";

                // pas de notification par mail
            }
            else {
                throw new RuntimeException("Action inattendue!");
            }

            $notificationLogs = $this->notificationService->getLogs();

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

        $viewModel->setTemplate('application/validation/these-corrigee/modifier-validation-correction');

        return $viewModel;
    }
}