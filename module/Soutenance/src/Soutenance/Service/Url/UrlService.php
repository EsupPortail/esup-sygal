<?php

namespace Soutenance\Service\Url;

use DateTime;
use Laminas\View\Renderer\PhpRenderer;
use Soutenance\Controller\PropositionController;

/**
 * TODO faire remonter un service père qui embarque la mécanique de base
 */
class UrlService {

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
     * @noinspection PhpUnusedAliasInspection
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
     * @noinspection PhpUnusedAliasInspection
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
     * @noinspection PhpUnusedAliasInspection
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
     * @noinspection PhpUnusedAliasInspection
     * @return string
     */
    public function getRapportTechnique() : string
    {
        $these = $this->variables['these'];
        /** @see \Soutenance\Controller\PresoutenanceController::rapportTechniqueAction() */
        $url = $this->renderer->url('soutenance/presoutenance/rapport-technique', ['these' => $these->getId()], ['force_canonical' => 'true'], true);
        return $url;
    }

}