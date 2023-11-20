<?php

namespace Soutenance\Service\Url;

use DateTime;
use Fichier\Controller\FichierController;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\PropositionController;
use Soutenance\Entity\Membre;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use These\Entity\Db\These;

/**
 * TODO faire remonter un service père qui embarque la mécanique de base
 */
class UrlService {
    use MembreServiceAwareTrait;

    /** @var PhpRenderer */
    protected $renderer;
    /** @var array */
    protected $variables;

    /**
     * @param PhpRenderer $renderer
     * @return UrlService
     */
    public function setRenderer($renderer) : UrlService
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @param array $variables
     * @return UrlService
     */
    public function setVariables(array $variables): UrlService
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @param string
     * @return mixed
     */
    public function getVariable(string $key)
    {
        if (! isset($this->variables[$key])) return null;
        return $this->variables[$key];
    }

    /**
     * @noinspection
     * @return string
     */
    public function getSoutenanceProposition() : string
    {
        $these = $this->variables['these'];
        /** @see PropositionController::propositionAction() */
        $url = $this->renderer->url('soutenance/proposition', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /** @noinspection PhpUnused  */
    public function getSoutenancePresoutenance() : string
    {
        $these = $this->variables['these'];
        /** @see PresoutenanceController::presoutenanceAction() */
        $url = $this->renderer->url('soutenance/presoutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getSermentDocteur() : string
    {
        $these = $this->variables['these'];
        /** @see PropositionController::genererSermentAction() */
        $url = $this->renderer->url('soutenance/proposition/generer-serment', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getProcesVerbal() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::procesVerbalSoutenanceAction() */
        $url = $this->renderer->url('soutenance/presoutenance/proces-verbal-soutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getRapportSoutenance() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::rapportSoutenanceAction() */
        $url = $this->renderer->url('soutenance/presoutenance/rapport-soutenance', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getRapportTechnique() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::rapportTechniqueAction() */
        $url = $this->renderer->url('soutenance/presoutenance/rapport-technique', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getUrlRapporteurDashboard() : string
    {
        /** @var These $these */
        $these = $this->variables['these'];
        /** @var Membre $rapporteur */
        $rapporteur = $this->variables['rapporteur'];
        if ($rapporteur->getActeur()) {
            $token = $this->getMembreService()->retrieveOrCreateToken($rapporteur);
            $url_rapporteur = $this->renderer->url("soutenance/index-rapporteur", ['these' => $these->getId()], ['force_canonical' => true], true);
            $url = $this->renderer->url('zfcuser/login', ['type' => 'token'], ['query' => ['token' => $token->getToken(), 'redirect' => $url_rapporteur, 'role' => $rapporteur->getActeur()->getRole()->getRoleId()], 'force_canonical' => true], true);
        } else {
            $url = $this->renderer->url('home');
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
        $url = $this->renderer->url('soutenance/avis-soutenance/telecharger',
            ['these' => $these->getId(),'rapporteur' => $membre->getId(),'avis' => $avis->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

    /** @noinspection PhpUnused  */
    public function getSoutenanceConvocationDoctorant() : string
    {
        $these = $this->variables['$these'];
        /** @see PresoutenanceController::convocationDoctorantAction() */
        $url = $this->renderer->url('soutenance/presoutenance/convocation-doctorant', ['these' => $these->getId()], ['force_canonical' => true], true);
        return $url;
    }

    /** @noinspection PhpUnused  */
    public function getSoutenanceConvocationMembre() : string
    {
        $these = $this->variables['these'];
        $membre = $this->variables['membre'];
        /** @see PresoutenanceController::convocationMembreAction() */
        $url = $this->renderer->url('soutenance/presoutenance/convocation-membre', ['these' => $these->getId(), 'membre' => $membre->getId()], ['force_canonical' => true], true);
        return $url;
    }

    /** @noinspection PhpUnused */
    public function generateTablePrerapport() : string
    {
        $these = $this->variables['these'];
        $soutenance = $this->variables['soutenance'];
        $rapporteurs = $soutenance->getRapporteurs();

        $texte  = "<table>";
        $texte .= "<tr><th>Rapporteur·trice</th><th>Pré-rapport</th></tr>";
        foreach ($rapporteurs as $rapporteur) {
            $url = $this->renderer->url('soutenance/avis-soutenance/telecharger',['these' => $these->getId(), 'rapporteur' => $rapporteur->getId()],['force_canonical' => true], true);
            $texte .= "<tr><td>".$rapporteur->getDenomination()."</td>";
            $texte .= "<td><a href='".$url."'>Prérapport</a></td>";
        }
        $texte .= "</table>";
        return $texte;

    }
}