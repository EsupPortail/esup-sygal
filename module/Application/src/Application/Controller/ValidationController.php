<?php

namespace Application\Controller;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Notification\ValidationDepotTheseCorrigeeNotification;
use Application\Notification\ValidationPageDeCouvertureNotification;
use Application\Notification\ValidationRdvBuNotification;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Notification\Notification;
use UnicaenApp\Exception\RuntimeException;
use Zend\View\Model\ViewModel;

class ValidationController extends AbstractController

{
    use TheseServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use UtilisateurServiceAwareTrait;

    public function pageDeCouvertureAction()
    {
        $these = $this->requestedThese();
        $result = $this->confirm()->execute();
        $action = $this->params()->fromQuery('action');

        // si un tableau est retourné par le plugin, l'opération a été confirmée
        if (is_array($result)) {
            $notification = new ValidationPageDeCouvertureNotification();
            $notification->setThese($these);
            $notification->setAction($action);

            if ($action === 'valider') {
                $this->validationService->validatePageDeCouverture($these);
                $successMessage = "Validation de la page de couverture enregistrée avec succès.";

                // notification
                $this->notifierService->trigger($notification);
            }
            elseif ($action === 'devalider') {
                $this->validationService->unvalidatePageDeCouverture($these);
                $successMessage ="Validation de la page de couverture annulée avec succès.";

                // notification
//                $this->notifierService->trigger($notification);
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

        $view->setTemplate('application/validation/page-de-couverture');

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
                $this->validationService->validateRdvBu($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification (doctorant: à la 1ere validation seulement)
                $notifierDoctorant = ! $this->validationService->existsValidationRdvBuHistorisee($these);
                $notification->setNotifierDoctorant($notifierDoctorant);
                $this->notifierService->triggerValidationRdvBu($notification);
            }
            elseif ($action === 'devalider') {
                $this->validationService->unvalidateRdvBu($these);
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

        $utilisateurs = [];
        /** @var Acteur $acteur */
        foreach ($these->getActeurs() as $acteur) {
            $individu = $acteur->getIndividu();
            $utilisateurs[$individu->getId()] = $this->utilisateurService->getRepository()->findByIndividu($individu); // ok
        }

        $view = new ViewModel([
            'these'          => $these,
            'validerUrl'     => $this->urlThese()->validerCorrectionTheseUrl($these),
            'devaliderUrl'   => $this->urlThese()->devaliderCorrectionTheseUrl($these),
            'typeValidation' => $this->validationService->getTypeValidation($type = TypeValidation::CODE_CORRECTION_THESE),
            'validations'    => $these->getValidations($type),
            'attendues'      => $this->validationService->getValidationsAttenduesPourCorrectionThese($these),
            'utilisateurs'   => $utilisateurs,
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
            if ($action === 'valider') {
                $validation = $this->validationService->validateDepotTheseCorrigee($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification des directeurs de thèse
                $this->notifierService->triggerValidationDepotTheseCorrigee($these);
            }
            elseif ($action === 'devalider') {
                $validation = $this->validationService->unvalidateDepotTheseCorrigee($these);
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
            if ($action === 'valider') {
                $this->assertIsAllowed($these, ValidationPrivileges::VALIDATION_CORRECTION_THESE);

                $validation = $this->validationService->validateCorrectionThese($these);
                $successMessage = "Validation enregistrée avec succès.";

                // notification par mail si plus aucune validation attendue
                $results = $this->validationService->getValidationsAttenduesPourCorrectionThese($these);
                if (count($results) === 0) {
                    $notif = new Notification();
                    $notif
                        ->setSubject("Validation des corrections de la thèse")
                        ->setTemplatePath('application/notification/mail/notif-validation-correction-these')
                        ->setTemplateVariables([
                            'these' => $these,
                            'role' => $this->roleService->getRepository()->findOneBy(['code' => Role::CODE_DIRECTEUR_THESE]),
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

                $validation = $this->validationService->unvalidateCorrectionThese($these);
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

        $viewModel->setTemplate('application/validation/these-corrigee/modifier-validation-correction');

        return $viewModel;
    }
}