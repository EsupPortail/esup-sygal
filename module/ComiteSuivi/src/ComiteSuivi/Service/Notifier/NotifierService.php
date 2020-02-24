<?php

namespace ComiteSuivi\Service\Notifier;

use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Service\Role\RoleServiceAwareTrait;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use ComiteSuivi\Entity\Db\CompteRendu;
use ComiteSuivi\Entity\Db\Membre;
use Notification\Notification;
use UnicaenApp\Exception\LogicException;

class NotifierService extends \Notification\Service\NotifierService {
    use RoleServiceAwareTrait;

    /**
     * @param These $these
     * @return string
     */
    protected function fetchEmailDoctorant(These $these)
    {
        $doctorant = $these->getDoctorant();
        $email = $doctorant->getIndividu()->getEmail();
        return $email;
    }

    /**
     * @param These $these
     * @return array
     */
    protected function fetchEmailEcoleDoctorale(These $these)
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            if ($individuRole->getIndividu()->getEmail() !== null)
                $emails[] = $individuRole->getIndividu()->getEmail();
        }
        return $emails;
    }

    /**
     * @param ComiteSuivi $comite
     */
    public function triggerFinalisation($comite)
    {
        $these = $comite->getThese();
        $doctorant = $these->getDoctorant();
        $emails = $this->fetchEmailEcoleDoctorale($these);

        if (! empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject($doctorant->getIndividu()->getNomComplet()." vient de finaliser son comité de suivi de thèse.")
                ->setTo($emails)
                ->setTemplatePath('comite-suivi/notification/finalisation')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'doctorant' => $doctorant,
                ]);
            $this->trigger($notif);
        }
    }
    /**
     * @param ComiteSuivi $comite
     */
    public function triggerValidation($comite)
    {
        $these = $comite->getThese();
        $ecole = $these->getEcoleDoctorale();
        $emails = $this->fetchEmailDoctorant($these);

        if (! empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("L'école doctorale (". $ecole->getSigle().") vient de valider votre comité de suivi de thèse.")
                ->setTo($emails)
                ->setTemplatePath('comite-suivi/notification/validation')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'ecole' => $ecole,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param ComiteSuivi $comite
     * @param string $motif
     */
    public function triggerRefus(ComiteSuivi $comite, $motif)
    {
        $these = $comite->getThese();
        $ecole = $these->getEcoleDoctorale();
        $emails = $this->fetchEmailDoctorant($these);

        if (! empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("L'école doctorale (". $ecole->getSigle().") vient de refuser votre comité de suivi de thèse.")
                ->setTo($emails)
                ->setTemplatePath('comite-suivi/notification/refus')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'ecole' => $ecole,
                    'raison' => $motif,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param ComiteSuivi $comite
     * @param Utilisateur $utilisateur
     * @param string $url
     */
    public function triggerInitialisationCompte($comite, $utilisateur, $url) {

        $email = $utilisateur->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Initialisation de votre compte SyGAL")
                ->setTo($email)
                ->setTemplatePath('comite-suivi/notification/init-compte')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'username' => $utilisateur->getUsername(),
                    'url' => $url,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param ComiteSuivi $comite
     * @param Membre $membre
     */
    public function triggerNotifierExaminateur(ComiteSuivi $comite, Membre $membre)
    {
        $these = $comite->getThese();
        $email = ($membre->getIndividu()->getEmail()?:$membre->getEmail());
        $doctorant = $these->getDoctorant();

        if (! empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Vous venez d'être désigné comme examinateur du comité de suivi de thèse de ".$doctorant->getIndividu()->getNomComplet().".")
                ->setTo($email)
                ->setTemplatePath('comite-suivi/notification/notifier-examinateur')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'doctorant' => $doctorant,
                    'membre' => $membre,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param CompteRendu $compterendu
     */
    public function triggerFinaliserCompteRendu(CompteRendu $compterendu)
    {
        $comite = $compterendu->getComite();
        $these = $comite->getThese();
        $emails = $this->fetchEmailEcoleDoctorale($these);
        $doctorant = $these->getDoctorant();

        if (! empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Un compte-rendu vient d'être finaliser pour le  comité de suivi de thèse de ".$doctorant->getIndividu()->getNomComplet().".")
                ->setTo($emails)
                ->setTemplatePath('comite-suivi/notification/finaliser-compterendu')
                ->setTemplateVariables([
                    'comite' => $comite,
                    'doctorant' => $doctorant,
                    'compterendu' => $compterendu,
                ]);
            $this->trigger($notif);
        }
    }


}