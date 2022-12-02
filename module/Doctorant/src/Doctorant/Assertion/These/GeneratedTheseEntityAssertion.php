<?php

namespace Doctorant\Assertion\These;

/**
 * Classe mère d'Assertion.
 *
 * Générée à partir du fichier
 * /home/gauthierb/workspace/sygal/module/Doctorant/data/TheseEntityAssertion.csv.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 29/11/2022 10:45:49
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

        if ($privilege === \Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 1 */
            $this->linesTrace[] = '/* line 1 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ &&
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 4 */) {
                $this->failureMessage = "Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 2 */
            $this->linesTrace[] = '/* line 2 */';
            return true;
        }

        if ($privilege === \Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 3 */
            $this->linesTrace[] = '/* line 3 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 4 */) {
                $this->failureMessage = "Vous ne pouvez pas modifier l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
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
class;Doctorant\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;4;;;
line;enabled;privilege;isRoleDoctorantSelected;isStructureDuRoleRespectee;isTheseEnCours;isUtilisateurEstAuteurDeLaThese;;return;message
1;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;1:1;;;2:0;;0;Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse
2;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;;;;;;1;
3;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT;1:1;;;2:0;;0;Vous ne pouvez pas modifier l’adresse de contact car vous n’êtes pas l’auteur de la thèse
4;1;\Doctorant\Provider\Privilege\DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT;;;;;;1;
EOT;
    }


}
