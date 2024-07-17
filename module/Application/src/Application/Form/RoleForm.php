<?php

namespace Application\Form;

use Laminas\Form\Form;


class RoleForm extends Form
{
    use \UnicaenAuth\Form\Droits\Traits\RoleFormAwareTrait;

//    public function __construct(){
//        parent::__construct();
//        var_dump(parent::getServiceRole());
//        var_dump($this->getServiceRole());
//        $this->setServiceRole(parent::getServiceRole());
//    }

    public function init(): void
    {
        $roleForm = $this->getFormFactory()->getFormElementManager()->get(\UnicaenAuth\Form\Droits\RoleForm::class);
        $roleForm->setName("role");
        $this->add($roleForm);
    }
}
