<?php

namespace Application\Service\Url;

use Application\Filter\IdifyFilterAwareTrait;
use Application\RouteMatch;
use InvalidArgumentException;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Router\RouteStackInterface;
use Traversable;
use UnicaenApp\Exception\RuntimeException;

abstract class UrlService
{
    use IdifyFilterAwareTrait;

    protected RouteStackInterface $router;
    protected ?RouteMatch $routeMatch = null;
    protected array $options = [];

    protected ?array $allowedVariables = null;
    protected array $variables;

    public function setOptions(array $options = []): static
    {
        $this->options = $options;
        return $this;
    }

    public function setVariables(array $variables): static
    {
        if ($this->allowedVariables !== null && ($diff = array_diff(array_keys($variables), $this->allowedVariables))) {
            throw new InvalidArgumentException(
                "Les variables suivantes ne sont pas autorisées explicitement : " . implode(', ', $diff)
            );
        }

        $this->variables = $variables;

        return $this;
    }

    /**
     * Generates a url given the name of a route.
     *
     * @param  string               $name               Name of the route
     * @param  array                $params             Parameters for the link
     * @param  array|Traversable    $options            Options for the route
     * @param  bool                 $reuseMatchedParams Whether to reuse matched parameters
     * @return string Url                         For the link href attribute
     */
    public function fromRoute($name = null, $params = array(), $options = array(), $reuseMatchedParams = false): string
    {
        $options = array_merge($this->options, $options);

        if (3 == func_num_args() && is_bool($options)) {
            $reuseMatchedParams = $options;
            $options = array();
        }

        if ($name === null) {
            if ($this->routeMatch === null) {
                throw new RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch->getMatchedRouteName();

            if ($name === null) {
                throw new RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        if (!is_array($params)) {
            if (!$params instanceof Traversable) {
                throw new \InvalidArgumentException(
                    'Params is expected to be an array or a Traversable object'
                );
            }
            $params = iterator_to_array($params);
        }

        if ($reuseMatchedParams && $this->routeMatch !== null) {
            $routeMatchParams = $this->routeMatch->getParams();

            if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($routeMatchParams, $params);
        }

        $options['name'] = $name;

        return $this->router->assemble($params, $options);
    }

    /**
     * Set the router to use for assembling.
     *
     * @param RouteStackInterface $router
     * @return self
     */
    public function setRouter(RouteStackInterface $router): static
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return self
     */
    public function setRouteMatch(RouteMatch $routeMatch): static
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }
}