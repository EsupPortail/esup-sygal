<?php

namespace RapportActivite\Rule\Operation\Notification;

use Application\Entity\Db\Role;
use Application\Rule\RuleInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Closure;
use Individu\Entity\Db\Individu;
use InvalidArgumentException;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Notification\RapportActiviteOperationAttenduNotification;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RuntimeException;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * Règles métiers concernant la notification en cas d'opération attendue sur un rapport d'activité.
 */
class OperationAttendueNotificationRule implements RuleInterface
{
    use RapportActiviteAvisServiceAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;
    use ActeurServiceAwareTrait;
    use RoleServiceAwareTrait;
    use MessageAwareTrait;

    private RapportActiviteOperationInterface $operationRealisee;
    private RapportActiviteOperationInterface $operationAttendue;
    private bool $notificationRequired;
    private array $to;
    private array $cc;
    private array $messagesByAvisValeurBool = [];
    private string $subject;
    private array $anomalies = [];

    private string $anomalieAucunePersonneTemplate =
        "Ce mail vous est adressé à la place des destinataires prévus car 
        les personnes ayant le rôle suivant sont introuvables dans l'application : %s.";
    private string $anomalieAucuneAdresseTemplate =
        "Ce mail vous est adressé à la place des destinataires prévus car aucune adresse 
        électronique n'a été trouvée dans l'application pour les personnes suivantes : %s.";

    private Closure $emailAddressExtractor;

    public function __construct()
    {
        $this->emailAddressExtractor = fn(Individu $i) => $i->getEmailContact() ?: $i->getEmailPro() ?: $i->getEmailUtilisateur();
    }

    public function setOperationRealisee(RapportActiviteOperationInterface $operationRealisee): self
    {
        $this->operationRealisee = $operationRealisee;
        return $this;
    }

    public function execute(): self
    {
        // On notifie à propos de l'opération suivante attendue, quelle qu'elle soit.

        $operationAttendue = $this->rapportActiviteOperationRule->findFollowingOperation($this->operationRealisee);
        if ($operationAttendue === null) {
            $this->notificationRequired = false;
            return $this;
        }

        $this->operationAttendue = $operationAttendue;

        $this->handleOperationAttendue();

        return $this;
    }

    private function handleOperationAttendue()
    {
        $these = $this->operationRealisee->getRapportActivite()->getThese();

        $followingOperationConfig = $this->rapportActiviteOperationRule->getConfigForOperation($this->operationAttendue);
        $to = $this->extractEmailsFromOperationRoles($followingOperationConfig);
        $cc = [$this->emailAddressExtractor->__invoke($these->getDoctorant()->getIndividu())];

        $this->notificationRequired = true;
        $this->to = $to;
        $this->cc = $cc;
        $this->subject = sprintf("%s de %s",
            $this->operationAttendue->getRapportActivite(),
            $these->getDoctorant()->getIndividu()->getNomComplet(),
        );
//        $this->subject = "Important : problème concernant votre rapport";
//        $this->messagesByAvisValeurBool = [
//            false => "<strong>Important : pour que votre École Doctorale valide votre rapport, vous devez " .
//                "prendre en compte les remarques ci-dessus et le redéposer (i.e. supprimer celui téléversé " .
//                "puis déposer le nouveau).</strong>",
//        ];
    }

    private function extractEmailsFromOperationRoles(array $operationConfig): array
    {
        $these = $this->operationRealisee->getRapportActivite()->getThese();
        $doctorant = $these->getDoctorant();

        // disposer de l'email du doctorant est le minimum !
        $emailDoctorant = $this->emailAddressExtractor->__invoke($doctorant->getIndividu());
        if (!$emailDoctorant) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour le doctorant {$doctorant}");
        }

        $emails = [];
        foreach ((array) $operationConfig['role'] as $code) {
            switch ($code) {
                case Role::CODE_DOCTORANT:
                    $emails[$emailDoctorant] = $doctorant . "(doctorant)";
                    break;

                case Role::CODE_DIRECTEUR_THESE:
                case Role::CODE_CODIRECTEUR_THESE:
                    $acteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($these, $code);
                    $emailsActeurs = $this->collectEmails($acteurs, $code, $emailDoctorant);
                    $emails = array_merge($emails, $emailsActeurs);
                    break;

                case Role::CODE_RESP_UR:
                case Role::CODE_RESP_ED:
                    $structure = $code === Role::CODE_RESP_UR ?
                        $these->getUniteRecherche()->getStructure() :
                        $these->getEcoleDoctorale()->getStructure();
                    // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                    $individusRoles =
                        $this->roleService->findIndividuRoleByStructure($structure, $code, $these->getEtablissement()) ?:
                        $this->roleService->findIndividuRoleByStructure($structure, $code);
                    $emailsIndividuRoles = $this->collectEmails($individusRoles, $code, $emailDoctorant);
                    $emails = array_merge($emails, $emailsIndividuRoles);
                    break;

                default:
                    throw new InvalidArgumentException("Cas imprévu");
            }
        }

        return $emails;
    }

    /**
     * @param \Individu\Entity\Db\IndividuRoleAwareInterface[] $individuRoleAwares
     * @param string $codeRole
     * @param string $emailDoctorantFallback
     * @return string[] email => identité
     */
    private function collectEmails(array $individuRoleAwares, string $codeRole, string $emailDoctorantFallback): array
    {
        $doctorant = $this->operationRealisee->getRapportActivite()->getThese()->getDoctorant();

        $emailsTmp = [];
        if (!count($individuRoleAwares)) {
            $role = $this->roleService->getRepository()->findByCode($codeRole);
            $emailsTmp[$emailDoctorantFallback] = $doctorant . "(doctorant)";
            $this->anomalies[] = sprintf($this->anomalieAucunePersonneTemplate, $role);
        } else {
            $identites = [];
            foreach ($individuRoleAwares as $ir) {
                $individu = $ir->getIndividu();
                $email = $this->emailAddressExtractor->__invoke($individu);
                $identite = sprintf("%s (%s)", $individu, $ir->getRole());
                $identites[] = $identite;
                $emailsTmp[$email] = $identite;
            }
            if (empty($emailsTmp)) {
                $emailsTmp[$emailDoctorantFallback] = $doctorant . "(doctorant)";
                $this->anomalies[] = sprintf($this->anomalieAucuneAdresseTemplate, implode(', ', $identites));
            }
        }

        return $emailsTmp;
    }

    public function configureNotification(RapportActiviteOperationAttenduNotification $notif)
    {
        $notif->setTo($this->to);
        $notif->setCc($this->cc);
        $notif->setSubject($this->subject);

        $notif->setOperationAttendue($this->operationAttendue);
        $notif->setAnomalies($this->anomalies);
    }

    public function isNotificationRequired(): bool
    {
        return $this->notificationRequired;
    }
}