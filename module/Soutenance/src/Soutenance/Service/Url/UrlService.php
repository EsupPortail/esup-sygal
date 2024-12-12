<?php

namespace Soutenance\Service\Url;

use RuntimeException;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\PropositionController;
use Soutenance\Entity\Membre;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\These;

class UrlService extends \Application\Service\Url\UrlService
{
    use MembreServiceAwareTrait;

    protected ?array $allowedVariables = [
        'these',
        'doctorant',
        'etablissement',
        'rapporteur',
        'avis',
        'membre',
        'soutenance',
        'validation',
    ];

//    public function setVariables(array $variables): UrlService
//    {
//        if ($this->allowedVariables !== null && ($diff = array_diff_key($variables, $this->allowedVariables))) {
//            throw new InvalidArgumentException(
//                "Les valiables suivantes ne sont pas autorisées explicitement : " . implode(', ', array_keys($diff))
//            );
//        }
//        $this->variables = $variables;
//        return $this;
//    }

    /**
     * @noinspection
     */
    public function getSoutenanceProposition() : string
    {
        $these = $this->variables['these'];
        /** @see PropositionController::propositionAction() */
        $url = $this->fromRoute('soutenance/proposition', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenancePresoutenance() : string
    {
        $these = $this->variables['these'];
        /** @see PresoutenanceController::presoutenanceAction() */
        $url = $this->fromRoute('soutenance/presoutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     */
    public function getSermentDocteur() : string
    {
        $these = $this->variables['these'];
        /** @see PropositionController::genererSermentAction() */
        $url = $this->fromRoute('soutenance/proposition/generer-serment', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     */
    public function getProcesVerbal() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::procesVerbalSoutenanceAction() */
        $url = $this->fromRoute('soutenance/presoutenance/proces-verbal-soutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     */
    public function getRapportSoutenance() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::rapportSoutenanceAction() */
        $url = $this->fromRoute('soutenance/presoutenance/rapport-soutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     */
    public function getRapportTechnique() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::rapportTechniqueAction() */
        $url = $this->fromRoute('soutenance/presoutenance/rapport-technique', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     */
    public function getUrlRapporteurDashboard() : string
    {
        /** @var These $these */
        $these = $this->variables['these'];
        /** @var Membre $rapporteur */
        $rapporteur = $this->variables['rapporteur'];
        if ($rapporteur->getActeur()) {
            $token = $this->getMembreService()->retrieveOrCreateToken($rapporteur);
            $url_rapporteur = $this->fromRoute("soutenance/index-rapporteur", ['these' => $these->getId()], ['force_canonical' => true], true);
            $url = $this->fromRoute('zfcuser/login', ['type' => 'token'], ['query' => ['token' => $token->getToken(), 'redirect' => $url_rapporteur, 'role' => $rapporteur->getActeur()->getRole()->getRoleId()], 'force_canonical' => true], true);
        } else {
            $url = $this->fromRoute('home');
        }
        return "<a href='".$url."' target='_blank'> Tableau de bord / Dashboard </a>";
    }

    /**
     * @noinspection PhpUnused
     */
    public function getPrerapportSoutenance() : string
    {
        $these = $this->variables['these'];
        $avis = $this->variables['avis'];
        $membre = $this->variables['membre'];
        /** @see AvisController::telechargerAction() */
        $url = $this->fromRoute('soutenance/avis-soutenance/telecharger',
            ['these' => $these->getId(),'rapporteur' => $membre->getId(),'avis' => $avis->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceConvocationDoctorant() : string
    {
        $these = $this->variables['these'];
        if ($these === null) { throw new RuntimeException("Aucune thèse fournie"); }
        /** @see PresoutenanceController::convocationDoctorantAction() */
        $url = $this->fromRoute('soutenance/presoutenance/convocation-doctorant', ['these' => $these->getId()], ['force_canonical' => true], true);
        return $url;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getSoutenanceConvocationMembre() : string
    {
        $these = $this->variables['these'];
        $membre = $this->variables['membre'];
        /** @see PresoutenanceController::convocationMembreAction() */
        $url = $this->fromRoute('soutenance/presoutenance/convocation-membre', ['these' => $these->getId(), 'membre' => $membre->getId()], ['force_canonical' => true], true);
        return $url;
    }

    /**
     * @noinspection PhpUnused
     */
    public function generateTablePrerapport() : string
    {
        $these = $this->variables['these'];
        $soutenance = $this->variables['soutenance'];
        $rapporteurs = $soutenance->getRapporteurs();

        $texte  = "<table>";
        $texte .= "<tr><th>Rapporteur·trice</th><th>Pré-rapport</th></tr>";
        foreach ($rapporteurs as $rapporteur) {
            $url = $this->fromRoute('soutenance/avis-soutenance/telecharger',['these' => $these->getId(), 'rapporteur' => $rapporteur->getId()],['force_canonical' => true], true);
            $texte .= "<tr><td>".$rapporteur->getDenomination()."</td>";
            $texte .= "<td><a href='".$url."'>Prérapport</a></td>";
        }
        $texte .= "</table>";
        return $texte;

    }
}