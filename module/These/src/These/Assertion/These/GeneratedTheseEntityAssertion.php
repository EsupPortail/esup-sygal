<?php

namespace These\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier
 * /home/gauthierb/workspace/sygal/module/These/data/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 29/11/2022 10:42:11
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
EOT;
    }


}
