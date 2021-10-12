<?php

namespace Application\View\Helper;

use Laminas\Http\Request;
use Laminas\Uri\Http;
use Laminas\View\Helper\AbstractHelper;

/**
 * Aide de vue générant une URL contenant les query parameters 'sort' et 'direction'
 * pour un critère de tri.
 *
 * @see $this->url();
 */
class Sortable extends AbstractHelper
{
    const ASC  = 'asc';
    const DESC = 'desc';

    /**
     * @var Http
     */
    private $targetUrl;

    /**
     * @var string
     */
    private $direction;

    /**
     * @var string
     */
    private $currentDirection;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     * @return Sortable
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Méthode d'invocation directe de l'aide de vue.
     *
     * @param string $sort Nom du critère de tri. Ex: "libelle", "nomUsuel".
     * @return self
     */
    public function __invoke(string $sort)
    {
        $this->targetUrl = clone $this->request->getUri();

        $params = $this->targetUrl->getQueryAsArray();

        $currentSort      = isset($params['sort']) ? $params['sort'] : '';
        $currentDirection = '';

        $params['sort'] = trim($sort);

        if ($sort !== $currentSort) {
            $direction = self::ASC; // direction par défaut
        }
        else {
            $currentDirection = isset($params['direction']) ? $params['direction'] : '';
            switch (mb_strtolower($currentDirection)) {
                case '':
                    $direction = self::ASC; // direction par défaut
                    break;
                case self::ASC:
                    $direction = self::DESC; // direction contraire
                    break;
                case self::DESC:
                    $direction = self::ASC; // direction contraire
                    break;
                default:
                    $direction = null;
                    break;
            }
        }

        $this->currentDirection = $currentDirection;

        $this->params = $params;
        $this->direction = $direction;

        return $this;
    }

    /**
     * Cf. méthode url().
     *
     * @return string
     */
    public function render()
    {
        return $this->url();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Retourne l'URL générée.
     *
     * Exemples avec $sort = 'libelle' :
     *
     *      URL de la requête courante             ==> URL générée                        | NB
     *      ------------------------------------------------------------------------------|----------------------------
     *      /these                                 ==> /these?sort=libelle&direction=asc  | Direction par défaut.
     *      /these?sort=autre&direction={asc|desc} ==> /these?sort=libelle&direction=asc  | Direction par défaut.
     *      /these?sort=libelle&direction=asc      ==> /these?sort=libelle&direction=desc | Direction contraire.
     *      /these?sort=libelle&direction=desc     ==> /these                             | Pas de tri.
     *
     * @return string
     */
    public function url()
    {
        if ($this->direction) {
            $this->params['direction'] = $this->direction;
        } else {
            if (isset($this->params['direction'])) {
                unset($this->params['direction']);
            }
            unset($this->params['sort']);
        }

        $this->targetUrl->setQuery($this->params);

        return $this->targetUrl->toString();
    }

    /**
     * Retourne la direction générée.
     *
     * @return string 'asc' ou 'desc'
     */
    public function dir()
    {
        return $this->direction;
    }

    /**
     * Retourne le code HTML de l'icône illustrant la direction courante.
     *
     * @return string
     */
    public function icon()
    {
        switch ($this->currentDirection) {
            case self::ASC:
                $title = "Trié(e)s par ordre croissant";
                $class = 'glyphicon glyphicon-sort-by-attributes';
                break;
            case self::DESC:
                $title = "Trié(e)s par ordre décroissant";
                $class = 'glyphicon glyphicon-sort-by-attributes-alt';
                break;
            default:
                $title = 'Non trié(s)';
                $class = '';
                break;
        }

        return sprintf('<span title="%s" class="sortable-icon %s"></span>', $title, $class);
    }
}