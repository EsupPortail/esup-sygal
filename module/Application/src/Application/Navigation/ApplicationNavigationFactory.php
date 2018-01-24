<?php

namespace Application\Navigation;

use Application\Entity\Db\These;
use Zend\Http\Request;
use Zend\Mvc\Router\RouteStackInterface as Router;
use Zend\Mvc\Router\RouteMatch;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

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
class ApplicationNavigationFactory extends NavigationFactory
{
    protected function handleParamsInjection(array &$page, RouteMatch $routeMatch = null)
    {
        if (!$routeMatch || !isset($page['withtarget']) || !isset($page['paramsInject'])) {
            return $this;
        }

        parent::handleParamsInjection($page, $routeMatch);

        /** @var \Application\RouteMatch $routeMatch */

        if (in_array('these', (array) $page['paramsInject'])) {
            $these = $routeMatch->getThese();

            if (isset($page['label'])) {
                $page['label'] = $this->subtituteTargetAttributesPatterns($page['label'], $these);
            }
            if (isset($page['class'])) {
                $page['class'] = $this->subtituteTargetAttributesPatterns($page['class'], $these);
            }
        }
        
        return $this;
    }

    /**
     * @param string $text
     * @param object $target
     * @return string
     */
    private function subtituteTargetAttributesPatterns($text, $target)
    {
        // recherche d'attributs entre accolades
        if (preg_match_all("!\{(.*)\}!Ui", $text, $matches)) {
            foreach ($matches[1] as $attr) {
                $method = 'get' . ucfirst($attr);
                /**
                 * Appel possible aux méthodes suivantes :
                 * - @see These::getCorrectionAutorisee()
                 */
                if (method_exists($target, $method)) {
                    $text = str_replace('{' . $attr . '}', strval($target->$method()), $text);
                }
            }
        }

        return $text;
    }
}