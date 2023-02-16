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
use Structure\Entity\Db\Structure;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;
use Webmozart\Assert\Assert;

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

    private string $anomalieRoleInexistantTemplate =
        "Ce mail vous est adressé à la place des destinataires prévus car 
        la recherche des personnes concernées est impossible ayant le rôle suivant sont introuvables dans l'application : %s.";
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
            // Aucune opération après : bye !
            $this->notificationRequired = false;
            return $this;
        }

        $operationAttendueConfig = $this->rapportActiviteOperationRule->getConfigForOperation($operationAttendue);
        $operationAttendueIsAuto = $operationAttendueConfig['readonly'] ?? false;
        if ($operationAttendueIsAuto) {
            // L'opération attendue ensuite est "automatique" (sans intervention humaine), personne à notifier : bye !
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
        foreach ((array) $operationConfig['role'] as $codeRole) {
            switch ($codeRole) {
                case Role::CODE_DOCTORANT:
                    $emails[$emailDoctorant] = $doctorant . "(doctorant)";
                    break;

                case Role::CODE_DIRECTEUR_THESE:
                case Role::CODE_CODIRECTEUR_THESE:
                    $acteurs = $this->acteurService->getRepository()->findActeursByTheseAndRole($these, $codeRole);
                    $emailsActeurs = $this->collectEmails($acteurs);
                    $emails = array_merge($emails, $emailsActeurs);
                    break;

                case Role::CODE_RESP_UR:
                case Role::CODE_RESP_ED:
                    $structure = ($codeRole === Role::CODE_RESP_UR) ?
                        $these->getUniteRecherche()->getStructure() :
                        $these->getEcoleDoctorale()->getStructure();
                    // Recherche des individus ayant le rôle attendu.
                    $individusRoles = $this->roleService->findIndividuRoleByStructure($structure, $codeRole, $these->getEtablissement());
                    if (!count($individusRoles)) {
                        // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                        $individusRoles = $this->roleService->findIndividuRoleByStructure($structure, $codeRole);
                    }
                    if (count($individusRoles)) {
                        $emailsIndividuRoles = $this->collectEmails($individusRoles);
                    } else {
                        $emailsIndividuRoles = $this->collectFallbackEmails($codeRole, $structure);
                    }
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
     * @return string[] email => identité
     */
    private function collectEmails(array $individuRoleAwares): array
    {
        Assert::notEmpty($individuRoleAwares);

        $doctorant = $this->operationRealisee->getRapportActivite()->getThese()->getDoctorant();
        $emailDoctorant = $this->emailAddressExtractor->__invoke($doctorant->getIndividu());

        $emailsTmp = [];
        $identites = [];
        foreach ($individuRoleAwares as $ir) {
            $individu = $ir->getIndividu();
            $email = $this->emailAddressExtractor->__invoke($individu);
            $identite = sprintf("%s (%s)", $individu, $ir->getRole());
            $identites[] = $identite;
            $emailsTmp[$email] = $identite;
        }
        if (empty($emailsTmp)) {
            $emailsTmp[$emailDoctorant] = $doctorant . "(doctorant)";
            $this->anomalies[] = sprintf($this->anomalieAucuneAdresseTemplate, implode(', ', $identites));
        }

        return $emailsTmp;
    }

    private function collectFallbackEmails(string $codeRole, Structure $structure): array
    {
        $doctorant = $this->operationRealisee->getRapportActivite()->getThese()->getDoctorant();
        $emailDoctorant = $this->emailAddressExtractor->__invoke($doctorant->getIndividu());

        // Aucun individu trouvé avec le rôle attendu, vérifions si le rôle attendu existe.
        $role = $this->roleService->getRepository()->findOneByCodeAndStructure($codeRole, $structure);
        if ($role === null) {
            // S'il n'existe pas, on recherche le 1er rôle existant ayant ce code, histoire de pouvoir informer.
            $role = $this->roleService->getRepository()->findByCode($codeRole);
        }
        if ($role === null) {
            throw new RuntimeException("Anomalie : aucun rôle trouvé avec ce code : '$codeRole'");
        }

        $emailsTmp = [];
        $emailsTmp[$emailDoctorant] = $doctorant . " (doctorant.e)";
        $this->anomalies[] = sprintf($this->anomalieAucunePersonneTemplate,
            $role->getLibelle() . ' ' . $structure->getSigle());

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