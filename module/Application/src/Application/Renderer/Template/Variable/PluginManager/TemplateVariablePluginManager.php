<?php

namespace Application\Renderer\Template\Variable\PluginManager;

use Application\Renderer\Template\Variable\TemplateVariableInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class TemplateVariablePluginManager extends AbstractPluginManager
{
    protected $instanceOf = TemplateVariableInterface::class;

    public function get($name, ?array $options = null): TemplateVariableInterface
    {
        /** @var TemplateVariableInterface $templateVariable */
        $templateVariable = parent::get($name, $options);
        $templateVariable->setPluginManager($this);

        return $templateVariable;
    }
}