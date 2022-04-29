<?php

namespace RapportActivite\Rule\Avis;

use Application\Rule\RuleInterface;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Notification\RapportActiviteAvisNotification;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RuntimeException;
use UnicaenApp\Traits\MessageAwareTrait;
use UnicaenAvis\Entity\Db\AvisValeur;

class RapportActiviteAvisNotificationRule implements RuleInterface
{
    use RapportActiviteAvisServiceAwareTrait;
    use MessageAwareTrait;

    private RapportActiviteAvis $rapportActiviteAvis;
    private bool $notificationRequired;
    private array $to;
    private array $messagesByAvisValeurBool;
    private string $subject;

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
        $nextAvisTypeForRapport =
            $this->rapportActiviteAvisService->findNextExpectedAvisTypeForRapport($this->rapportActiviteAvis->getRapportActivite());

        // s'agit-il du dernier avis attendu ?
        $isLastAvisTypeForRapport = null === $nextAvisTypeForRapport;

        $avisValeur = $this->rapportActiviteAvis->getAvis()->getAvisValeur();
        $these = $this->rapportActiviteAvis->getRapportActivite()->getThese();

        switch (true) {
            case !$isLastAvisTypeForRapport &&
                $avisValeur->getValeurBool() === false:

                // notifier doctorant
                $this->notificationRequired = true;
                $this->to = [$these->getDoctorant()->getEmail() => $these->getDoctorant()->getIndividu()->getNomComplet()];
                $this->subject = "Votre rapport d'activité";
                $this->messagesByAvisValeurBool = [
                    false => '<strong>Important : pour que votre École Doctorale valide votre rapport, vous devez prendre en compte les remarques ci-dessus et le redéposer.</strong>',
                ];

                break;

            case $isLastAvisTypeForRapport &&
                $avisValeur->getCode() === RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET:

                // notifier l'auteur de l'avis précédent
                $rapportActiviteAvisPrec = $this->fetchPreviousAvis();
                $auteurPrec = $rapportActiviteAvisPrec->getHistoModificateur() ?: $rapportActiviteAvisPrec->getHistoCreateur();
                $auteurPrecEmail = $auteurPrec->getIndividu() ? $auteurPrec->getIndividu()->getEmailBestOf() : $auteurPrec->getEmail();

                $this->notificationRequired = true;
                $this->to = [$auteurPrecEmail => $auteurPrec->getDisplayName()];
                $this->subject = "Rapport d'activité de " . $these->getDoctorant();
                $this->messagesByAvisValeurBool = [
                    false => '<strong>Important : le rapport étant incomplet, la balle revient dans votre camp !!</strong>',
                ];

                break;

            default:
                $this->notificationRequired = false;
        }

        return $this;
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

    public function isNotificationPossible(): bool
    {
        return $this->notificationRequired;
    }

    /**
     * @return array
     */
    public function getTo(): array
    {
        return $this->to;
    }
}