<?php

namespace Admission\Service\Url;

use Laminas\View\Renderer\PhpRenderer;

class UrlService {

    protected PhpRenderer $renderer;
    protected array $variables;

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
    public function getAdmission() : string
    {
        $admission = $this->variables['admission'];
        $link = $this->renderer->url('admission/ajouter', ['action' => 'etudiant', 'individu' => $admission->getIndividu()->getId()], ['force_canonical' => true], true);
        $url = "<a href='" . $link . "'>lien</a>";

        return $url;
    }
}