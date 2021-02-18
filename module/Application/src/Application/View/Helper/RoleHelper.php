<?php

namespace Application\View\Helper;

use Application\Entity\Db\Role;
use Zend\Form\View\Helper\AbstractHelper;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplatePathStack;

class RoleHelper extends AbstractHelper
{
    /**
     * @param Role $role
     * @param array $options
     * @return string
     */
    public function render(Role $role, $options = []) {

        /** @var PhpRenderer $view */
//        $view = $this->getView();
//        $view->resolver()->attach(new TemplatePathStack(['script_paths' => [__DIR__ . "/partial"]]));
//        $texte = $view->partial('role', ['role' => $role, 'options' => $options]);
        $texte = $role->getLibelle();
        if ($role->getStructure()) $texte .= " " . $role->getStructure()->getSigle();
        return $texte;
    }
}