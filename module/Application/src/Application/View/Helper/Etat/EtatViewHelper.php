<?php

namespace Application\View\Helper\Etat;

use Admission\Entity\Db\Etat;
use Application\View\Renderer\PhpRenderer;
use HDR\Entity\Db\HDR;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;
use These\Entity\Db\These;

class EtatViewHelper extends AbstractHelper
{
    /**
     * Génère la représentation d'un état dans la vue.
     *
     * @param array<string, string>|object|null $etat  Soit un tableau ['libelle' => ..., 'code' => ...], soit une entité Etat*
     * @param int|null $resultat Facultatif. Utile pour afficher le résultat de la thèse ($these->getResultat)
     * @param string|null $resultatString Chaîne correspondant à une description plus longue du résultat, ($these->getResultatToString)
     * @param array{
     *      afficherLibelleLong?: bool,
     *      textTooltip?: string,
     *      css?: string
     *  } $options Options d'affichage supplémentaires.
     *
     * @return string   Le rendu HTML de l'état.
     */
    public function __invoke(mixed $etat = null, int|null $resultat = null, string|null $resultatString = "", $options = []): string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $afficherLibelleLong = isset($options['afficherLibelleLong']) && $options['afficherLibelleLong'] === true ?? false;
        $css = $this->getCss($etat, $resultat, $afficherLibelleLong);
        $css .= $options['css'] ?? '';
        $iconEtatClass = $this->getIconEtatClass($etat, $resultat);
        $textTooltip = $options["textTooltip"] ?? $this->getEtatTextTooltip($etat, $resultat, $resultatString);

        return $this->view->partial('etat.phtml', [
            'etat' => $etat,
            'css' => $css,
            'iconEtatClass' => $iconEtatClass,
            'textTooltip' => $textTooltip,
            'etatLibelle' => is_array($etat) ? $etat["libelle"] : $etat->getLibelle(),
            'options' => $options
        ]);
    }

    private function getCss(mixed $etat, int|null $resultat, bool $libelleLong): string
    {
        $etatCode = is_array($etat) ? $etat["code"] : $etat->getCode();
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
            case HDR::ETAT_SOUTENANCE_PROGRAMMEE :
            case \Soutenance\Entity\Etat::EN_COURS_SAISIE:
                $baseColor = "#ffba00";
                break;
            case Etat::CODE_EN_COURS_VALIDATION :
            case These::ETAT_EN_COURS :
            case \Soutenance\Entity\Etat::EN_COURS_EXAMEN:
                $baseColor = "#f1732d";
                break;
            case Etat::CODE_REJETE:
            case Etat::CODE_ABANDONNE:
            case These::ETAT_ABANDONNEE:
            case \Soutenance\Entity\Etat::REJETEE:
                $baseColor = "#d23544";
                break;
            case These::ETAT_TRANSFEREE :
                $baseColor = "grey";
                break;
            case Etat::CODE_VALIDE :
            case \Soutenance\Entity\Etat::VALIDEE :
                $baseColor = "var(--success-color)";
                break;
            case \Soutenance\Entity\Etat::ETABLISSEMENT :
                $baseColor = "teal";
                break;
            case \Soutenance\Entity\Etat::COMPLET :
                $baseColor = "dodgerblue";
                break;
            case These::ETAT_SOUTENUE :
                if(!$resultat){
                    $baseColor = "var(--primary-color)";
                }else{
                    $baseColor = $resultat === 1 ? "var(--success-color)" : "#d23544d23544;";
                }
                break;
            default:
                $baseColor = "";
        }
        if ($libelleLong) {
            $style = "color:$baseColor; border-left: 5px solid {$baseColor};";
        } else {
            $style = "background-color:{$baseColor};";
        }

        return $style;
    }

    private function getIconEtatClass(mixed $etat, int|null $resultat): string
    {
        $etatCode = is_array($etat) ? $etat["code"] : $etat->getCode();
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
            case \Soutenance\Entity\Etat::EN_COURS_SAISIE:
            $iconEtat = "icon icon-edit";
                break;
            case HDR::ETAT_SOUTENANCE_PROGRAMMEE :
                $iconEtat = "fas fa-clock";
                break;
            case Etat::CODE_EN_COURS_VALIDATION :
            case These::ETAT_EN_COURS :
            case \Soutenance\Entity\Etat::EN_COURS_EXAMEN:
            case \Soutenance\Entity\Etat::COMPLET :
                $iconEtat = "icon icon-hourglass";
                break;
            case Etat::CODE_ABANDONNE:
            case These::ETAT_ABANDONNEE:
                $iconEtat = "icon icon-unchecked";
                break;
            case These::ETAT_TRANSFEREE :
                $iconEtat = "icon icon-export";
                break;
            case Etat::CODE_REJETE:
            case \Soutenance\Entity\Etat::REJETEE :
                $iconEtat = "icon icon-ko";
                break;
            case Etat::CODE_VALIDE :
            case \Soutenance\Entity\Etat::VALIDEE :
                $iconEtat = "icon icon-ok";
                break;
            case \Soutenance\Entity\Etat::ETABLISSEMENT:
                $iconEtat = "icon icon-checked";
                break;
            case These::ETAT_SOUTENUE :
                if(!$resultat){
                    $iconEtat = "icon icon-question";
                }else{
                    $iconEtat = $resultat === 1 ? "icon icon-ok" : "icon icon-ko";
                }
                break;
            default:
                $iconEtat = "";
        }

        return $iconEtat;
    }

    private function getEtatTextTooltip(mixed $etat, int|null $resultat, string $resultatString): string
    {
        $etatCode = is_array($etat) ? $etat["code"] : $etat->getCode();
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
            case These::ETAT_EN_COURS :
                $etatTextTooltip = $etat instanceof Etat ? $etat->getLibelle() : "En cours";
                break;
            case HDR::ETAT_SOUTENANCE_PROGRAMMEE :
                $etatTextTooltip = "Soutenance programmée";
                break;
            case Etat::CODE_VALIDE:
            case Etat::CODE_REJETE:
            case Etat::CODE_EN_COURS_VALIDATION :
                $etatTextTooltip = $etat->getLibelle();
                break;
            case Etat::CODE_ABANDONNE:
            case These::ETAT_ABANDONNEE:
                $etatTextTooltip = "Abandonnée";
                break;
            case These::ETAT_TRANSFEREE :
                $etatTextTooltip = "Transférée";
                break;
            case These::ETAT_SOUTENUE :
                if(!$resultat){
                    $etatTextTooltip = "Soutenue (Résultat inconnu)";
                }else{
                    $etatTextTooltip = $resultatString;
                }
                break;
            default:
                $etatTextTooltip = "";
        }

        return $etatTextTooltip;
    }

    public function __toString()
    {
        return '<div>État par défaut</div>';
    }
}