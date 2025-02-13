<?php

namespace These\Service\Notification;

use Application\Service\Email\EmailServiceAwareTrait;
use Depot\Notification\PasDeMailPresidentJury;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use Acteur\Entity\Db\ActeurThese;
use These\Entity\Db\These;
use These\Notification\ChangementsResultatsThesesNotification;
use These\Notification\ResultatTheseAdmisDoctorantNotification;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class TheseNotificationFactory extends NotificationFactory
{
    use EmailServiceAwareTrait;

    /**
     * Crée la notification concernant des changements quelconques de résultats de thèses.
     *
     * @param array $data Données concernant les thèses dont le résultat a changé
     */
    public function createNotificationChangementResultatThesesGestionnaires(array $data): Notification
    {
        $these = current($data)['these'];

        $emailsBdd = $this->emailService->fetchEmailAspectsDoctorat($these);
        if (empty($emailsBdd)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la MDD (thèse {$these->getId()})");
        }

        $emailsBu = $this->emailService->fetchEmailAspectsBibliotheque($these);
        if (empty($emailsBu)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la BU (thèse {$these->getId()})");
        }

        $notif = new ChangementsResultatsThesesNotification();
        $notif->setData($data);
        $notif->setTo(array_merge($emailsBdd, $emailsBu));

        return $notif;
    }

    /**
     * Crée les notifications à propos de résultats de thèses passés à 'Admis'.
     *
     * @return \Notification\Notification[]
     */
    public function createNotificationsChangementResultatThesesAdmisDoctorant(array $data): array
    {
        $notifs = [];

        foreach ($data as $array) {
            $these = $array['these'];
            /* @var These $these */

            $emailsBdd = $this->emailService->fetchEmailAspectsDoctorat($these);
            if (empty($emailsBdd)) {
                throw new RuntimeException("Aucune adresse mail trouvée pour la Maison du doctorat (thèse {$these->getId()})");
            }

            $notif = new ResultatTheseAdmisDoctorantNotification();
            $notif->setThese($these);
            $notif->setEmailsBdd($emailsBdd);

            $notifs[] = $notif;
        }

        return $notifs;
    }


    /**
     * Notification à propos du dépassement de la date butoir de dépôt de la version corrigée de la thèse.
     *
     * @param These $these
     * @return \Notification\Notification
     */
    public function createNotificationDateButoirCorrectionDepassee(These $these): Notification
    {
        $to = $this->emailService->fetchEmailAspectsDoctorat($these);
        if (empty($to)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la Maison du doctorat (thèse {$these->getId()})");
        }

        $notif = new Notification();
        $notif
            ->setSubject("Corrections " . lcfirst($these->getCorrectionAutoriseeToString(true)) . " non faites")
            ->setTo($to)
            ->setTemplatePath('depot/depot/mail/notif-date-butoir-correction-depassee')
            ->setTemplateVariables([
                'these' => $these,
            ]);

        return $notif;
    }


    /**
     * Notification à propos de l'absence de mail connu pour le président du jury.
     *
     * @param These $these
     * @param ActeurThese $president
     * @return \Notification\Notification
     */
    public function createNotificationPasDeMailPresidentJury(These $these, ActeurThese $president): Notification
    {
        $emailsBdd = $this->emailService->fetchEmailAspectsDoctorat($these);
        if (empty($emailsBdd)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la Maison du doctorat (thèse {$these->getId()})");
        }

        $notif = new PasDeMailPresidentJury();
        $notif
            ->setThese($these)
            ->setEmailsBdd($emailsBdd)
            ->setPresident($president)
            ->setTemplateVariables([
                'these' => $these,
                'president' => $president,
            ]);

        return $notif;
    }

}