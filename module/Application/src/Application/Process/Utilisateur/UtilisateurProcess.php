<?php

namespace Application\Process\Utilisateur;

use Application\Entity\Db\Utilisateur;
use Application\Process\AbstractProcess;
use Application\Service\Notification\ApplicationNotificationFactoryAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Exception;
use Individu\Service\IndividuServiceAwareTrait;
use Notification\Exception\RuntimeException;
use Notification\Service\NotifierServiceAwareTrait;
use UnicaenAuth\Service\Traits\UserServiceAwareTrait;

class UtilisateurProcess extends AbstractProcess
{
    use UtilisateurServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use UserServiceAwareTrait;

    use ApplicationNotificationFactoryAwareTrait;
    use NotifierServiceAwareTrait;

    /**
     * Création d'un compte local et de l'individu lié, à partir des données
     * du formulaire {@see \Application\Form\CreationUtilisateurForm}.
     *
     * @param array $formData
     * @return \Application\Entity\Db\Utilisateur
     * @throws \Application\Process\Utilisateur\UtilisateurProcessException Pb rencontré
     */
    public function createUtilisateurAndIndividuFromFormData(array $formData): Utilisateur
    {
        $createur = $this->utilisateurService->fetchAppPseudoUtilisateur();

        $this->entityManager->beginTransaction();

        try {
            $individu = $this->individuService->newIndividuFromData($formData, $createur);
            $this->individuService->saveIndividu($individu);

            $utilisateur = $this->utilisateurService->createFromIndividu($individu, $individu->getEmailPro(), 'none');
            $this->userService->updateUserPasswordResetToken($utilisateur);

            $notif = $this->applicationNotificationFactory->createNotificationInitialisationCompte($utilisateur);
            $result = $this->notifierService->trigger($notif);
            if (!$result->isSuccess()) {
                throw new RuntimeException($result->getErrorMessages()[0]);
            }

            $this->entityManager->commit();
        }
        catch (RuntimeException $e) {
            $this->entityManager->rollback();
            throw new UtilisateurProcessException(
                "La notification n'a pas pu être envoyée : " . $e->getMessage(),
                null,
                $e);
        }
        catch (Exception $e) {
            $this->entityManager->rollback();
            throw new UtilisateurProcessException(
                "Un problème est survenu lors de la création du compte local : " . $e->getMessage(),
                null,
                $e
            );
        }

        return $utilisateur;
    }
}