<?php

namespace Admission\Service\Url;

use Admission\Entity\Db\Admission;
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
     * @noinspection
     * @return string
     */
    public function getAdmission() : string
    {
        $admission = $this->variables['admission'];
        if($admission instanceof Admission){
            $individu = $admission->getIndividu() ;
        }else {
            $individu = $admission->getAdmission()->getIndividu();
        }
        $link = $this->renderer->url('admission/ajouter', ['action' => 'etudiant', 'individu' => $individu->getId()], ['force_canonical' => true, 'query' => ['refresh' => 'true']], true);

        $url = "<a href='" . $link . "'>lien</a>";

        return $url;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getAccueilAdmission() : string
    {
        $link = $this->renderer->url('admission', ['action' => 'index'], ['force_canonical' => true], true);
        $url = "<a href='" . $link . "'>lien</a>";

        return $url;
    }
}