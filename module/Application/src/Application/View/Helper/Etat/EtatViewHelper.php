<?php

namespace Application\View\Helper\Etat;

use Admission\Entity\Db\Etat;
use Application\View\Renderer\PhpRenderer;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Resolver\TemplatePathStack;
use These\Entity\Db\These;

class EtatViewHelper extends AbstractHelper
{
    public function __invoke(string|Etat $etat = null, int|null $resultat = null, string $resultatString): string
    {
        /** @var PhpRenderer $view */
        $view = $this->getView();
        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));

        $colorClass = $this->getColorClass($etat, $resultat);
        $iconEtatClass = $this->getIconEtatClass($etat, $resultat);
        $etatTextTooltipClass = $this->getEtatTextTooltipClass($etat, $resultat, $resultatString);

        return $this->view->partial('etat.phtml', [
            'colorClass' => $colorClass,
            'iconEtatClass' => $iconEtatClass,
            'etatTextTooltipClass' => $etatTextTooltipClass
        ]);
    }

    private function getColorClass(string|Etat $etat, int|null $resultat): string
    {
        $etatCode = $etat instanceof Etat ? $etat->getCode() : $etat;
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
                $colorEtat = "#ffba00";
                break;
            case Etat::CODE_EN_COURS_VALIDATION :
            case These::ETAT_EN_COURS :
                $colorEtat = "#f1732d";
                break;
            case Etat::CODE_REJETE:
            case Etat::CODE_ABANDONNE:
            case These::ETAT_ABANDONNEE:
                $colorEtat = "#d23544";
                break;
            case These::ETAT_TRANSFEREE :
                $colorEtat = "grey";
                break;
            case Etat::CODE_VALIDE :
                $colorEtat = "var(--success-color)";
                break;
            case These::ETAT_SOUTENUE :
                if(!$resultat){
                    $colorEtat = "var(--primary-color)";
                }else{
                    $colorEtat = $resultat === 1 ? "var(--success-color)" : "#d23544";
                }
                break;
            default:
                $colorEtat = "";
        }

        return $colorEtat;
    }

    private function getIconEtatClass(string|Etat $etat, int|null $resultat): string
    {
        $etatCode = $etat instanceof Etat ? $etat->getCode() : $etat;
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
                $iconEtat = "edit";
                break;
            case Etat::CODE_EN_COURS_VALIDATION :
            case These::ETAT_EN_COURS :
                $iconEtat = "hourglass";
                break;
            case Etat::CODE_ABANDONNE:
            case These::ETAT_ABANDONNEE:
                $iconEtat = "unchecked";
                break;
            case These::ETAT_TRANSFEREE :
                $iconEtat = "export";
                break;
            case Etat::CODE_REJETE:
                $iconEtat = "ko";
                break;
            case Etat::CODE_VALIDE :
                $iconEtat = "ok";
                break;
            case These::ETAT_SOUTENUE :
                if(!$resultat){
                    $iconEtat = "question";
                }else{
                    $iconEtat = $resultat === 1 ? "ok" : "ko";
                }
                break;
            default:
                $iconEtat = "";
        }

        return $iconEtat;
    }

    private function getEtatTextTooltipClass(string|Etat $etat, int|null $resultat, string $resultatString): string
    {
        $etatCode = $etat instanceof Etat ? $etat->getCode() : $etat;
        switch ($etatCode){
            case Etat::CODE_EN_COURS_SAISIE :
            case These::ETAT_EN_COURS :
                $etatTextTooltip = $etat instanceof Etat ? $etat->getLibelle() : "En cours";
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
}