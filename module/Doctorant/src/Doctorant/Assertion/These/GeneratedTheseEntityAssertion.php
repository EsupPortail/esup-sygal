<?php

namespace Doctorant\Assertion\These;

use Application\Assertion\Exception\UnexpectedPrivilegeException as UnexpectedPrivilegeException;
use Doctorant\Provider\Privilege\DoctorantPrivileges as DoctorantPrivileges;

/**
 * Classe mère d'Assertion.
 *
 * Générée avec la ligne de commande 'bin/assertions/generate-assertion -f
 * module/Doctorant/data/TheseEntityAssertion.csv'.
 *
 * @author Application\Assertion\Generator\AssertionGenerator
 * @date 21/11/2023 15:59:14
 */
abstract class GeneratedTheseEntityAssertion
{
    protected ?string $failureMessage;

    protected array $linesTrace = [
        
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

        if ($privilege === DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 1 */
            $this->linesTrace[] = '/* line 1 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 2 */) {
                $this->failureMessage = "Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 2 */
            $this->linesTrace[] = '/* line 2 */';
            return true;
        }

        if ($privilege === DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT) {
        //--------------------------------------------------------------------------------------
            /* line 3 */
            $this->linesTrace[] = '/* line 3 */';
            if ($this->isRoleDoctorantSelected() /* test 1 */ && 
                ! $this->isUtilisateurEstAuteurDeLaThese() /* test 2 */) {
                $this->failureMessage = "Vous ne pouvez pas modifier l’adresse de contact car vous n’êtes pas l’auteur de la thèse";
                return false;
            }
            /* line 4 */
            $this->linesTrace[] = '/* line 4 */';
            return true;
        }

        if ($privilege === DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN) {
        //--------------------------------------------------------------------------------------
            /* line 5 */
            $this->linesTrace[] = '/* line 5 */';
            if (! $this->isUtilisateurConnaitIndividu() /* test 3 */) {
                $this->failureMessage = "Vous ne pouvez pas visualiser cette fiche doctorant";
                return false;
            }
            /* line 6 */
            $this->linesTrace[] = '/* line 6 */';
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
    abstract protected function isUtilisateurEstAuteurDeLaThese() : bool;
    /**
     * @return bool
     */
    abstract protected function isUtilisateurConnaitIndividu() : bool;
    /**
     * Retourne le contenu du fichier CSV à partir duquel a été générée cette
     * classe.
     *
     * @return string
     */
    public function loadedFileContent() : string
    {
        return <<<'EOT'
use;Doctorant\Provider\Privilege\DoctorantPrivileges;DoctorantPrivileges;;;;;
class;Doctorant\Assertion\These\GeneratedTheseEntityAssertion;;1;2;3;;
line;enabled;privilege;isRoleDoctorantSelected;isUtilisateurEstAuteurDeLaThese;isUtilisateurConnaitIndividu;return;message
1;1;DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;1:1;2:0;;0;Vous ne pouvez pas visualiser l’adresse de contact car vous n’êtes pas l’auteur de la thèse
2;1;DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT;;;;1;
3;1;DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT;1:1;2:0;;0;Vous ne pouvez pas modifier l’adresse de contact car vous n’êtes pas l’auteur de la thèse
4;1;DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT;;;;1;
5;1;DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN;;;1:0;0;Vous ne pouvez pas visualiser cette fiche doctorant
6;1;DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN;;;;1;
EOT;
    }
}
