<?php

namespace These\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier /app/module/These/data/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 21/11/2022 10:32:48
 */
abstract class GeneratedTheseEntityAssertion
{

    protected $failureMessage;

    protected $linesTrace = [
        
    ];

    /**
     * Retourne true si le privilège spécifié est accordé ; false sinon.
     *
     * @param string $privilege
     * @return bool
     */
    public function assertAsBoolean($privilege)
    {
        $this->failureMessage = null;

        if ($privilege === \These\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE) {
        //--------------------------------------------------------------------------------------
            /* line 1 */
            $this->linesTrace[] = '/* line 1 */';
            return true;
        }

        if ($privilege) {
        //--------------------------------------------------------------------------------------
            /* line 2 */
            $this->linesTrace[] = '/* line 2 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 22 */) {
                $this->failureMessage = "Cette thèse n'est pas la vôtre.";
                return false;
            }
            /* line 3 */
            $this->linesTrace[] = '/* line 3 */';
            if (! $this->isStructureDuRoleRespectee() /* test 3 */) {
                $this->failureMessage = "Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.";
                return false;
            }
        }

        if ($privilege === \Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 81 */
            $this->linesTrace[] = '/* line 81 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 22 */) {
                $this->failureMessage = "Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 82 */
            $this->linesTrace[] = '/* line 82 */';
            return true;
        }

        throw new \Application\Assertion\Exception\UnexpectedPrivilegeException(
            "Le privilège spécifié n'est pas couvert par l'assertion: $privilege. Trace : " . PHP_EOL . implode(PHP_EOL, $this->linesTrace));
    }

    /**
     * @return bool
     */
    abstract protected function isRoleDoctorantSelected();
    /**
     * @return bool
     */
    abstract protected function isStructureDuRoleRespectee();
    /**
     * @return bool
     */
    abstract protected function isTheseEnCours();
    /**
     * @return bool
     */
    abstract protected function isTheseSoutenue();
    /**
     * @return bool
     */
    abstract protected function isUtilisateurEstAuteurDeLaThese();
    /**
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent()
    {
        return <<<'EOT'
class;These\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;5;6;21;22;;;
line;enabled;privilege;isRoleDoctorantSelected;;isStructureDuRoleRespectee;;isTheseEnCours;isTheseSoutenue;;isUtilisateurEstAuteurDeLaThese;;return;message
1;1;\These\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE;;;;;;;;;;1;
2;1;*;1:1;;;;;;;2:0;;0;Cette thèse n'est pas la vôtre.
3;1;*;;;1:0;;;;;;;0;Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.
81;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;1:1;;;;;;;2:0;;0;Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse
82;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;;;;;;;;;;1;
EOT;
    }


}
