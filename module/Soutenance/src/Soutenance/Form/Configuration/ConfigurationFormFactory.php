<?php

namespace Soutenance\Form\Configuration;

use Zend\Form\FormElementManager;

class ConfigurationFormFactory {

    public function __invoke(FormElementManager $manager)
    {
        /** @var ConfigurationForm $form */
        $form = new ConfigurationForm();
        return $form;
    }
}