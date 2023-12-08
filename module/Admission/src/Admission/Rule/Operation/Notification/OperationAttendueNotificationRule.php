<?php

namespace Admission\Rule\Operation\Notification;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Notification\AdmissionOperationAttenduNotification;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Application\Entity\Db\Role;
use Application\Rule\RuleInterface;
use Application\Service\Role\RoleServiceAwareTrait;
use Closure;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Individu\Entity\Db\IndividuRoleAwareInterface;
use InvalidArgumentException;
use RuntimeException;
use Structure\Entity\Db\StructureConcreteInterface;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;
use Webmozart\Assert\Assert;

/**
 * Règles métiers concernant la notification en cas d'opération attendue sur un dossier d'admission.
 */
class OperationAttendueNotificationRule implements RuleInterface
{
    use AdmissionOperationRuleAwareTrait;
    use ActeurServiceAwareTrait;
    use RoleServiceAwareTrait;
    use MessageAwareTrait;

    private AdmissionOperationInterface $operationRealisee;
    private AdmissionOperationInterface $operationAttendue;
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
        $this->emailAddressExtractor = fn($i) => ($i instanceof Individu)
            ? ($i->getEmailContact() ?: $i->getEmailPro() ?: $i->getEmailUtilisateur())
            : '';
    }

    public function setOperationRealisee(AdmissionOperationInterface $operationRealisee): self
    {
        $this->operationRealisee = $operationRealisee;
        return $this;
    }

    public function execute(): self
    {
        // On notifie à propos de l'opération suivante attendue, quelle qu'elle soit.

        $operationAttendue = $this->admissionOperationRule->findFollowingOperation($this->operationRealisee);
        if ($operationAttendue === null) {
            // Aucune opération après : bye !
            $this->notificationRequired = false;
            return $this;
        }

        $operationAttendueConfig = $this->admissionOperationRule->getConfigForOperation($operationAttendue);
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
        $admission = $this->operationRealisee->getAdmission();

        $followingOperationConfig = $this->admissionOperationRule->getConfigForOperation($this->operationAttendue);
        $to = $this->extractEmailsFromOperationRoles($followingOperationConfig);
        $cc = [$this->emailAddressExtractor->__invoke($admission->getIndividu())];

        $this->notificationRequired = true;
        $this->to = $to;
        $this->cc = $cc;

        $this->subject = sprintf("%s de %s",
            $this->operationAttendue->getAdmission(),
            $admission->getIndividu()->getNomComplet(),
        );
    }

    private function extractEmailsFromOperationRoles(array $operationConfig): array
    {
        $admission = $this->operationRealisee->getAdmission();
        $etudiant = $admission->getIndividu();

        // disposer de l'email de l'étudiant est le minimum !
        $emailEtudiant = $this->emailAddressExtractor->__invoke($etudiant);
        if (!$emailEtudiant) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$etudiant}");
        }

        $emails = [];
        foreach ((array) $operationConfig['role'] as $codeRole) {
            switch ($codeRole) {
                case Role::CODE_ADMIN_TECH:
                    $emails["thomas.hamel@unicaen.fr"] = "Thomas Hamel";
                    break;
                case Role::CODE_DOCTORANT:
                    $emails[$emailEtudiant] = $etudiant . "(étudiant)";
                    break;

                case Role::CODE_GEST_ED:
                case Role::CODE_GEST_UR:
                    $structureConcrete = ($codeRole === Role::CODE_GEST_UR) ?
                        $admission->getInscription()->first()->getUniteRecherche() :
                        $admission->getInscription()->first()->getEcoleDoctorale();
                    // Recherche des individus ayant le rôle attendu.
                    $individusRoles = !empty($structureConcrete) ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole, $admission->getInscription()->first()->getComposanteDoctorat()) : null;
                    if (!empty($individusRoles) && !count($individusRoles)) {
                        // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                        $individusRoles = $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole);
                    }
                    if (!empty($individusRoles) && count($individusRoles)) {
                        $emailsIndividuRoles = $this->collectEmails($individusRoles);
                    } else {
                        $emailsIndividuRoles = $this->collectFallbackEmails($codeRole, $structureConcrete);
                    }
                    $emails = array_merge($emails, $emailsIndividuRoles);
                    break;

                case Role::CODE_DIRECTEUR_THESE:
                case Role::CODE_CODIRECTEUR_THESE:
                    $acteurs[] = ($codeRole === Role::CODE_DIRECTEUR_THESE) ?
                        $admission->getInscription()->first()->getDirecteur() :
                        $admission->getInscription()->first()->getCoDirecteur();
                    if (count($acteurs)) {
                        $emailsActeurs = $this->collectEmails($acteurs);
                    } else {
                        $emailsActeurs = $this->collectFallbackEmails($codeRole);
                    }
                    $emails = array_merge($emails, $emailsActeurs);
                    break;

                case Role::CODE_RESP_UR:
                case Role::CODE_RESP_ED:
                    $structureConcrete = ($codeRole === Role::CODE_RESP_UR) ?
                        $admission->getInscription()->first()->getUniteRecherche() :
                        $admission->getInscription()->first()->getEcoleDoctorale();
                    // Recherche des individus ayant le rôle attendu.
                    $individusRoles = !empty($structureConcrete) ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole, $admission->getInscription()->first()->getComposanteDoctorat()) : null;
                    if (!empty($individusRoles) && !count($individusRoles)) {
                        // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                        $individusRoles = $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole);
                    }
                    if (!empty($individusRoles) && count($individusRoles)) {
                        $emailsIndividuRoles = $this->collectEmails($individusRoles);
                    } else {
                        $emailsIndividuRoles = $this->collectFallbackEmails($codeRole, $structureConcrete);
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
     * Collecte des emails/identités des destinataires de secours.
     * La liste des anomalies est également mise à jour en conséquences.
     *
     * @param string $codeRole
     * @param StructureConcreteInterface|null $structureConcrete
     * @return string[] email => identité
     */
    private function collectFallbackEmails(string $codeRole, ?StructureConcreteInterface $structureConcrete = null): array
    {
        $roleToString = $this->getRoleAttenduToString($codeRole, $structureConcrete);

        $etudiant = $this->operationRealisee->getAdmission()->getIndividu();
        $emailEtudiant = $this->emailAddressExtractor->__invoke($etudiant);

        $emailsTmp = [];
        $emailsTmp[$emailEtudiant] = $etudiant . " (étudiant(e))";
        $this->anomalies[] = sprintf($this->anomalieAucunePersonneTemplate, $roleToString);

        return $emailsTmp;
    }

    private function getRoleAttenduToString(string $codeRole, ?StructureConcreteInterface $structureConcrete = null): string
    {
        $role = null;
        $roleToString = null;

        // Si une structure est spécifiée, on cherche un rôle "structure dépendant".
        if ($structureConcrete !== null) {
            $role = $this->roleService->getRepository()->findOneByCodeAndStructure($codeRole, $structureConcrete->getStructure());
            $roleToString = $role ? (string) $role : null;
        }
        // Si on n'a aucun rôle sous la main, on recherche le 1er rôle existant ayant ce code, juste pour son libellé.
        if ($role === null) {
            $role = $this->roleService->getRepository()->findByCode($codeRole);
            $roleToString = $role ? $role->getLibelle() : null; // NB : faut bien prendre le libellé
        }

        if ($roleToString === null) {
            // Cas très peu probable.
            $roleToString = $codeRole;
        }

        if ($structureConcrete !== null) {
            $roleToString .= ' ' . $structureConcrete;
        }

        return $roleToString;
    }

    /**
     * Extraction des emails/identités à partir des données individus/rôles.
     * La liste des anomalies est également mise à jour en cas d'email introuvable pour un individu.
     *
     * @param IndividuRoleAwareInterface[] $individuRoleAwares
     * @return string[] email => identité
     */
    private function collectEmails(array $individuRoleAwares): array
    {
        Assert::notEmpty($individuRoleAwares);

        $etudiant =  $this->operationRealisee->getAdmission()->getIndividu();
        $emailEtudiant = $this->emailAddressExtractor->__invoke($etudiant);

        $emailsTmp = [];
        $identites = [];
        foreach ($individuRoleAwares as $ir) {
            $individu = ($ir instanceof IndividuRole) ? $ir->getIndividu() : $ir;
//            $identite = sprintf("%s (%s)", $individu, $ir->getRole());
            $identite = sprintf("%s", $individu);
            $identites[] = $identite;
            $email = $this->emailAddressExtractor->__invoke($individu);
            if ($email) {
                $emailsTmp[$email] = $identite;
            }
        }
        if (!$emailsTmp) {
            $emailsTmp[$emailEtudiant] = $etudiant . "(étudiant(e))";
            $this->anomalies[] = sprintf($this->anomalieAucuneAdresseTemplate, implode(', ', $identites));
        }

        return $emailsTmp;
    }

    public function configureNotification(AdmissionOperationAttenduNotification $notif)
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