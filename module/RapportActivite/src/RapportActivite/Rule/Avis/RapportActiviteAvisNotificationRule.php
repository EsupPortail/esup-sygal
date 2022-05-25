<?php

namespace RapportActivite\Rule\Avis;

use Application\Rule\RuleInterface;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Notification\RapportActiviteAvisNotification;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RuntimeException;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * Application des règles métiers concernant la notification en cas d'avis apporté sur un rapport d'activité.
 */
class RapportActiviteAvisNotificationRule implements RuleInterface
{
    use RapportActiviteAvisServiceAwareTrait;
    use MessageAwareTrait;

    private RapportActiviteAvis $rapportActiviteAvis;
    private bool $notificationRequired;
    private array $to;
    private array $messagesByAvisValeurBool;
    private string $subject;

    /**
     * Spécifie l'avis sur lequel tester les règles métiers.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @return $this
     */
    public function setRapportActiviteAvis(RapportActiviteAvis $rapportActiviteAvis): self
    {
        $this->rapportActiviteAvis = $rapportActiviteAvis;
        return $this;
    }

    /**
     * @return static
     */
    public function execute(): self
    {
        $this->notificationRequired = false;

        $avisType = $this->rapportActiviteAvis->getAvis()->getAvisType();

        switch ($avisType->getCode()) {
            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_GEST:
                $this->onAvisGestionnaire();
                break;
            case RapportActiviteAvis::AVIS_TYPE__CODE__AVIS_RAPPORT_ACTIVITE_DIR:
                $this->onAvisResponsable();
                break;
        }

        return $this;
    }

    private function onAvisGestionnaire()
    {
        $avisValeur = $this->rapportActiviteAvis->getAvis()->getAvisValeur();
        $these = $this->rapportActiviteAvis->getRapportActivite()->getThese();

        if ($avisValeur->getValeurBool() === false) {
            // notifier doctorant
            $this->notificationRequired = true;
            $this->to = [$these->getDoctorant()->getEmail() => $these->getDoctorant()->getIndividu()->getNomComplet()];
            $this->subject = "Important : problème concernant votre rapport";
            $this->messagesByAvisValeurBool = [
                false => "<strong>Important : pour que votre École Doctorale valide votre rapport, vous devez " .
                    "prendre en compte les remarques ci-dessus et le redéposer (i.e. supprimer celui téléversé " .
                    "puis déposer le nouveau).</strong>",
            ];
        }
    }

    private function onAvisResponsable()
    {
        $avisValeur = $this->rapportActiviteAvis->getAvis()->getAvisValeur();
        $these = $this->rapportActiviteAvis->getRapportActivite()->getThese();

        if ($avisValeur->getCode() === RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET) {
            // notifier l'auteur de l'avis précédent
            $rapportActiviteAvisPrec = $this->fetchPreviousAvis();
            $auteurPrec = $rapportActiviteAvisPrec->getHistoModificateur() ?: $rapportActiviteAvisPrec->getHistoCreateur();
            $auteurPrecEmail = $auteurPrec->getIndividu() ? $auteurPrec->getIndividu()->getEmailBestOf() : $auteurPrec->getEmail();

            $this->notificationRequired = true;
            $this->to = [$auteurPrecEmail => $auteurPrec->getDisplayName()];
            $this->subject = "Rapport d'activité de " . $these->getDoctorant();
            $this->messagesByAvisValeurBool = [
                false => "<strong>Important : le rapport étant incomplet, la balle revient dans votre camp.</strong> " .
                    "Vous devez revenir sur votre avis et déclarer le rapport incomplet afin de notifier le doctorant.",
            ];
        }
    }

    public function configureNotification(RapportActiviteAvisNotification $notif)
    {
        $notif->setTo($this->to);
        $notif->setSubject($this->subject);
        $notif->setMessagesByAvisValeurBool($this->messagesByAvisValeurBool);
    }

    private function fetchPreviousAvis(): RapportActiviteAvis
    {
        $previousAvisTypeForRapport =
            $this->rapportActiviteAvisService->findPreviousAvisTypeForRapport($this->rapportActiviteAvis->getRapportActivite());

        if ($previousAvisTypeForRapport === null) {
            throw new RuntimeException("Anomalie : aucun type d'avis précédent trouvé");
        }

        return $this->rapportActiviteAvisService->findRapportAvisByRapportAndAvisType(
            $this->rapportActiviteAvis->getRapportActivite(),
            $previousAvisTypeForRapport);
    }

    public function isNotificationRequired(): bool
    {
        return $this->notificationRequired;
    }
}