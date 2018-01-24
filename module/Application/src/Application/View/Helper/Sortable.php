<?php

namespace Application\View\Helper;

use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Uri\Http;
use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue générant une URL contenant les query parameters 'sort' et 'direction'
 * pour un critère de tri.
 *
 * @see $this->url();
 */
class Sortable extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

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
     * Méthode d'invocation directe de l'aide de vue.
     *
     * @param string $sort Nom du critère de tri. Ex: "libelle", "nomUsuel".
     * @return self
     */
    public function __invoke($sort)
    {
        /** @var \Zend\View\HelperPluginManager $pluginManager */
        $pluginManager = $this->getServiceLocator();
        $serviceManager = $pluginManager->getServiceLocator();

        /** @var Request $request */
        $request = $serviceManager->get('request');

        $this->targetUrl = clone $request->getUri();

        $params = $this->targetUrl->getQueryAsArray();

        $currentSort      = isset($params['sort']) ? $params['sort'] : '';
        $currentDirection = '';

        $params['sort'] = trim($sort);

        if ($sort !== $currentSort) {
            $direction = self::ASC;
        }
        else {
            $currentDirection = isset($params['direction']) ? $params['direction'] : '';
            switch (mb_strtolower($currentDirection)) {
                case '':
                    $direction = self::ASC;
                    break;
                case self::ASC:
                    $direction = self::DESC;
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
     *      URL de la requête courante                  ==> URL générée
     *      -----------------------------------------------------------------------------------------------------------
     *      /these                                      ==> /these?sort=libelle&direction=asc
     *      /these?sort=autre&direction={asc|desc}      ==> /these?sort=libelle&direction=asc
     *      /these?sort=libelle&direction=asc           ==> /these?sort=libelle&direction=desc
     *      /these?sort=libelle&direction=desc          ==> /these
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