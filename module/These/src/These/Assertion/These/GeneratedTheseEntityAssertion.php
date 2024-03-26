<?php

namespace These\Assertion\These;

use Application\Assertion\Exception\UnexpectedPrivilegeException as UnexpectedPrivilegeException;

/**
 * Classe mère d'Assertion.
 *
 * Générée avec la ligne de commande 'bin/assertions/generate-assertion --file
 * module/These/data/TheseEntityAssertion.csv'.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 25/03/2024 12:45:24
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
    public function assertAsBoolean(string $privilege) : bool
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

        if ($privilege === \These\Provider\Privilege\ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE) {
        //--------------------------------------------------------------------------------------
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 22 */) {
                $this->failureMessage = "Vous ne pouvez pas saisir le(s) domaines HAL car vous n’êtes pas l’auteur de la thèse.";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
            if (! $this->isStructureDuRoleRespectee() /* test 3 */) {
                $this->failureMessage = "Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.";
                return false;
            }
            /* line 7 */
            $this->linesTrace[] = '/* line 7 */';
            if (! $this->isTheseEnCours() /* test 5 */) {
                $this->failureMessage = "L’état de la thèse ne permet pas cette opération.";
                return false;
            }
            /* line 8 */
            $this->linesTrace[] = '/* line 8 */';
            return true;
        }

        throw new UnexpectedPrivilegeException(
            "Le privilège spécifié n'est pas couvert par l'assertion: $privilege. Trace : " . PHP_EOL . implode(PHP_EOL, $this->linesTrace));
    }

    /**
     * @return bool
     */
    abstract protected function isRoleDoctorantSelected() : bool;
    /**
     * @return bool
     */
    abstract protected function isStructureDuRoleRespectee() : bool;
    /**
     * @return bool
     */
    abstract protected function isTheseEnCours() : bool;
    /**
     * @return bool
     */
    abstract protected function isTheseSoutenue() : bool;
    /**
     * @return bool
     */
    abstract protected function isUtilisateurEstAuteurDeLaThese() : bool;
    /**
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent() : string
    {
        return <<<'EOT'
class;These\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;5;6;21;22;;;
line;enabled;privilege;isRoleDoctorantSelected;;isStructureDuRoleRespectee;;isTheseEnCours;isTheseSoutenue;;isUtilisateurEstAuteurDeLaThese;;return;message
1;1;\These\Provider\Privilege\ThesePrivileges::THESE_TOUT_FAIRE;;;;;;;;;;1;
2;1;*;1:1;;;;;;;2:0;;0;Cette thèse n'est pas la vôtre.
3;1;*;;;1:0;;;;;;;0;Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.
5;1;\These\Provider\Privilege\ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE;1:1;;;;;;;2:0;;0;Vous ne pouvez pas saisir le(s) domaines HAL car vous n’êtes pas l’auteur de la thèse.
6;1;\These\Provider\Privilege\ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE;;;1:0;;;;;;;0;Votre profil dans l’application ne vous permet pas d’agir sur cette thèse.
7;1;\These\Provider\Privilege\ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE;;;;;1:0;;;;;0;L’état de la thèse ne permet pas cette opération.
8;1;\These\Provider\Privilege\ThesePrivileges::THESE_MODIFICATION_DOMAINES_HAL_THESE;;;;;;;;;;1;
EOT;
    }
}
