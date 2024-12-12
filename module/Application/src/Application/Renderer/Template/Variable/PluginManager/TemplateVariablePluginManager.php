<?php

namespace Application\Renderer\Template\Variable\PluginManager;

use Application\Renderer\Template\Variable\TemplateVariableInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class TemplateVariablePluginManager extends AbstractPluginManager
{
    protected $instanceOf = TemplateVariableInterface::class;
}