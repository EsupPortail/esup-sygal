<?php

namespace Admission\Rule\Email;

use Admission\Entity\Db\Admission;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleServiceAwareTrait;
use Closure;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuRole;
use Individu\Entity\Db\IndividuRoleAwareInterface;
use InvalidArgumentException;
use RuntimeException;
use Structure\Entity\Db\StructureConcreteInterface;
use UnicaenApp\Traits\MessageAwareTrait;
use Webmozart\Assert\Assert;

/**
 * Règles métiers concernant la notification en cas d'opération attendue sur un dossier d'admission.
 */
class ExtractionEmailRule
{
    use RoleServiceAwareTrait;
    use MessageAwareTrait;
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

    public function getAnomalies(): array
    {
        return $this->anomalies;
    }

    public function extractEmailsFromAdmissionRoles(Admission $admission, array $roles): array
    {
        $etudiant = $admission->getIndividu();

        // disposer de l'email de l'étudiant est le minimum !
        $emailEtudiant = $this->emailAddressExtractor->__invoke($etudiant);
        if (!$emailEtudiant) {
            throw new RuntimeException("Anomalie bloquante : aucune adresse mail disponible pour l'étudiant {$etudiant}");
        }

        $emails = [];
        foreach ((array) $roles['role'] as $codeRole) {
            switch ($codeRole) {
                case Role::CODE_ADMIN_TECH:
                    break;
                case Role::ROLE_ID_USER:
                    $emails[$emailEtudiant] = $etudiant . "(étudiant)";
                    break;

                case Role::CODE_GEST_ED:
                case Role::CODE_GEST_UR:
                    $structureConcrete = ($codeRole === Role::CODE_GEST_UR) ?
                        $admission->getInscription()->first()->getUniteRecherche() :
                        $admission->getInscription()->first()->getEcoleDoctorale();
                    // Recherche des individus ayant le rôle attendu.
                    $individusRoles = !empty($structureConcrete) ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole) : null;
                    if (empty($individusRoles)) {
                        // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                        $individusRoles = $structureConcrete ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole) : null;
                    }
                    if (!empty($individusRoles) && count($individusRoles)) {
                        $emailsIndividuRoles = $this->collectEmails($admission, $individusRoles);
                    } else {
                        $emailsIndividuRoles = $this->collectFallbackEmails($admission, $codeRole, $structureConcrete);
                    }
                    $emails = array_merge($emails, $emailsIndividuRoles);
                    break;

                case Role::CODE_DIRECTEUR_THESE:
                case Role::CODE_CODIRECTEUR_THESE:
                case Role::CODE_ADMISSION_DIRECTEUR_THESE:
                case Role::CODE_ADMISSION_CODIRECTEUR_THESE:
                    $acteurs[] = ($codeRole === Role::CODE_DIRECTEUR_THESE || $codeRole === Role::CODE_ADMISSION_DIRECTEUR_THESE) ?
                        $admission->getInscription()->first()->getDirecteur() :
                        $admission->getInscription()->first()->getCoDirecteur();
                    if (count($acteurs)) {
                        $emailsActeurs = $this->collectEmails($admission, $acteurs);
                    } else {
                        $emailsActeurs = $this->collectFallbackEmails($admission,$codeRole);
                    }
                    $emails = array_merge($emails, $emailsActeurs);
                    break;

                case Role::CODE_RESP_UR:
                case Role::CODE_RESP_ED:
                    $structureConcrete = ($codeRole === Role::CODE_RESP_UR) ?
                        $admission->getInscription()->first()->getUniteRecherche() :
                        $admission->getInscription()->first()->getEcoleDoctorale();
                    // Recherche des individus ayant le rôle attendu.
                    $individusRoles = !empty($structureConcrete) ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole) : null;
                    if (empty($individusRoles)) {
                        // Si aucun individu n'est trouvé avec la contrainte sur l'établissement de l'individu, on essaie sans.
                        $individusRoles = !empty($structureConcrete) ? $this->roleService->findIndividuRoleByStructure($structureConcrete->getStructure(), $codeRole) : null;
                    }
                    if (!empty($individusRoles) && count($individusRoles)) {
                        $emailsIndividuRoles = $this->collectEmails($admission,$individusRoles);
                    } else {
                        $emailsIndividuRoles = $this->collectFallbackEmails($admission, $codeRole, $structureConcrete);
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
     * @param Admission $admission
     * @param string $codeRole
     * @param StructureConcreteInterface|null $structureConcrete
     * @return string[] email => identité
     */
    private function collectFallbackEmails(Admission $admission, string $codeRole, ?StructureConcreteInterface $structureConcrete = null): array
    {
        $roleToString = $this->getRoleAttenduToString($codeRole, $structureConcrete);

        $etudiant = $admission->getIndividu();
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
            $roleToString = $role?->getLibelle(); // NB : faut bien prendre le libellé
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
    private function collectEmails(Admission $admission, array $individuRoleAwares): array
    {
        Assert::notEmpty($individuRoleAwares);

        $etudiant =  $admission->getIndividu();
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
}