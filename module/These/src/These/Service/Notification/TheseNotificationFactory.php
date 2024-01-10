<?php

namespace These\Service\Notification;

use Application\Service\Email\EmailTheseServiceAwareTrait;
use Depot\Notification\ChangementCorrectionAttendueNotification;
use Depot\Notification\PasDeMailPresidentJury;
use Depot\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Import\Model\ImportObservResult;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use These\Entity\Db\Acteur;
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
    use EmailTheseServiceAwareTrait;

    /**
     * Crée la notification concernant des changements quelconques de résultats de thèses.
     *
     * @param array $data Données concernant les thèses dont le résultat a changé
     */
    public function createNotificationChangementResultatThesesGestionnaires(array $data): Notification
    {
        $these = current($data)['these'];

        $emailsBdd = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        if (empty($emailsBdd)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la MDD (thèse {$these->getId()})");
        }

        $emailsBu = $this->emailTheseService->fetchEmailAspectsBibliotheque($these);
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

            $emailsBdd = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
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
     * Notification à propos de corrections attendues.
     *
     * @param ImportObservResult $record
     * @param These $these
     * @param string|null $message
     * @return \Notification\NotificationResult|null
     */
    public function createNotificationCorrectionAttendue(ImportObservResult $record, These $these, ?string &$message = null): ?Notification
    {
        // interrogation de la règle métier pour savoir comment agir...
        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule
            ->setThese($these)
            ->setDateDerniereNotif($record->getDateNotif())
            ->execute();
        $message = $rule->getMessage(' ');
        $estPremiereNotif = $rule->estPremiereNotif();
        $dateProchaineNotif = $rule->getDateProchaineNotif();

        if ($dateProchaineNotif === null) {
            return null;
        }

        $dateProchaineNotif->setTime(0, 0, 0);
        $now = (new \DateTime())->setTime(0, 0, 0);

        if ($now != $dateProchaineNotif) {
            return null;
        }

        $notif = new ChangementCorrectionAttendueNotification();
        $notif
            ->setThese($these)
            ->setEstPremiereNotif($estPremiereNotif);

        return $notif;
    }

    /**
     * Notification à propos du dépassement de la date butoir de dépôt de la version corrigée de la thèse.
     *
     * @param These $these
     * @return \Notification\Notification
     */
    public function createNotificationDateButoirCorrectionDepassee(These $these): Notification
    {
        $to = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
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
     * @param Acteur $president
     * @return \Notification\Notification
     */
    public function createNotificationPasDeMailPresidentJury(These $these, Acteur $president): Notification
    {
        $emailsBdd = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
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