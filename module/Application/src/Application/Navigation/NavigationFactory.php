<?php

namespace Application\Navigation;

use Interop\Container\ContainerInterface;
use LogicException;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Router\RouteMatch;

/**
 * Factory de navigation prenant en charge :
 * - l'attribut de page supplémentaire 'pagesProvider' spécifiant un service fournisseur de pages filles ;
 * - la spécification d'un service à interroger pour savoir si un page doit être masquée ou non ;
 * - l'injection éventuelle de paramètres de page à partir du RouteMatch courant.
 * 
 * @todo À déplacer dans UnicaenApp.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NavigationFactory extends DefaultNavigationFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->container = $container;

        return parent::__invoke($container, $requestedName, $options);
    }
    
    /**
     * @inheritDoc
     */
    protected function injectComponents(array $pages, $routeMatch = null, $router = null, $request = null)
    {
        //
        foreach ($pages as &$page) {
            $hasUri = isset($page['uri']);
            $hasMvc = isset($page['action']) || isset($page['controller']) || isset($page['route']);
            if ($hasMvc) {
                if (!isset($page['routeMatch']) && $routeMatch) {
                    $page['routeMatch'] = $routeMatch;
                }
                if (!isset($page['router'])) {
                    $page['router'] = $router;
                }
            } elseif ($hasUri) {
                if (!isset($page['request'])) {
                    $page['request'] = $request;
                }
            }
            //
        
            $this->processPage($page, $routeMatch);

            //
            if (isset($page['pages'])) {
                $page['pages'] = $this->injectComponents($page['pages'], $routeMatch, $router, $request);
            }
            //
        }
        
        return $pages;
    }

    /**
     * @param array $page
     * @param RouteMatch|null $routeMatch
     */
    protected function processPage(array &$page, RouteMatch $routeMatch = null)
    {
        // l'attribut 'pagesProvider' d'une page peut être le nom d'un fournisseur de pages filles
        if ($this->canHandlePagesProvider($page)) {
            $this->handlePagesProvider($page);
        }

        // l'attribut 'visible' d'une page peut être le nom d'un service
        if ($this->canHandleVisibility($page, $routeMatch)) {
            $this->handleVisibility($page, $routeMatch);
        }

        // injections éventuelles de paramètres de page à partir du RouteMatch
        if ($this->canHandleParamsInjection($page)) {
            $this->handleParamsInjection($page, $routeMatch);
        }
    }

    /**
     * @param array $page
     * @return bool
     */
    protected function canHandlePagesProvider($page)
    {
        return isset($page['pagesProvider']);
    }

    /**
     * Recherche de l'attribut de page 'pagesProvider' pouvant spécifier un 
     * fournisseur de pages filles.
     * 
     * @param array $page
     * @throws LogicException
     */
    protected function handlePagesProvider(&$page)
    {
        $pagesProviderAttr = $page['pagesProvider'];
        $params = [];
        
        // l'attribut 'pagesProvider' d'une page peut être le nom d'un fournisseur de pages filles
        if (is_string($pagesProviderAttr)) {
            
        }
        elseif (is_array($pagesProviderAttr) && isset($pagesProviderAttr['type'])) {
            $type = $pagesProviderAttr['type'];
            unset($pagesProviderAttr['type']);
            $params = array_merge($params, $pagesProviderAttr);
            $pagesProviderAttr = $type;
        }
        else {
            throw new LogicException("Format d'attribut 'pagesProvider' incorrect!");
        }
        
        $pagesProvider = $this->container->get($pagesProviderAttr);
        if (!is_callable($pagesProvider)) {
            throw new LogicException(
                    "Service spécifié pour l'attribut de page 'pagesProvider' non valide : $pagesProviderAttr.");
        }
        
        $children = $pagesProvider($page, $params);
        if (!isset($page['pages'])) {
            $page['pages'] = [];
        }
        
        $page['pages'] = array_merge($children, $page['pages']); // NB: possibilité d'écraser une page fille issue du fournisseur
    }

    /**
     * @param array $page
     * @param RouteMatch|null $routeMatch
     * @return bool
     */
    protected function canHandleVisibility(array $page, RouteMatch $routeMatch = null)
    {
        // l'attribut 'visible' d'une page peut être le nom d'un service
        return isset($page['visible']) && is_string($page['visible']);
    }

    /**
     * Recherche de l'attribut 'visible' d'une page peut être aussi le nom d'un service
     * (au lieu d'un booléen comme le fonctionnement standard).
     *
     * @param array $page
     * @param RouteMatch|null $routeMatch
     */
    protected function handleVisibility(&$page, RouteMatch $routeMatch = null)
    {
        $visible = $this->container->get($page['visible']);
        if (!is_callable($visible)) {
            throw new LogicException(
                    "Service spécifié pour l'attribut de page 'visible' non valide : {$page['visible']}.");
        }
        $page['visible'] = $visible($page, $routeMatch);
    }

    /**
     * @param array $page
     * @return bool
     */
    protected function canHandleParamsInjection(array $page)
    {
        return isset($page['withtarget']) && isset($page['paramsInject']);
    }

    /**
     * Injection éventuelle de paramètres du RouteMatch dans la page courante.
     *
     * Rappel liminaire :
     * Si une page utilise une route nécessitant obligatoirement un paramètre 'x',
     * il faut fournir à cette page ce paramètre sinon on obtient l'erreur 'Missing parameter "x"'
     * au moment du rendu du menu.
     *
     * Si la page possède l'attribut spécial 'paramsInject', que le paramètre 'x' est listé
     * par cet attribut spécial, et que le paramètre 'x' existe dans la requête courante (route match),
     * alors on l'injecte dans la page.
     *
     * NB: Si ce paramètre n'est pas présent dans la requête, on doit masquer la page pour
     * éviter l'erreur 'Missing parameter "x"'.
     *
     * @param array $page
     * @param RouteMatch|null $routeMatch
     * @return self
     */
    protected function handleParamsInjection(array &$page, RouteMatch $routeMatch = null)
    {
        foreach ((array) $page['paramsInject'] as $param) {
            if ($routeMatch && ($id = $routeMatch->getParam($param))) {
                $page['params'][$param] = $id;
            }
            else {
                $page['visible'] = false;
            }
        }
        
        return $this;
    }
}