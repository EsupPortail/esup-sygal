<?php

namespace Application\Form;

use Zend\Form\Element\Submit;
use Zend\Form\Form;
use Application\Entity\Db\MailConfirmation;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;
use Zend\InputFilter\Factory;

class MailConfirmationForm extends Form {

    /**
     * NB: hydrateur injecté par la factory
     */
    public function init()
    {
        $this->setObject(new MailConfirmation());

        $this->add(new Hidden('id'));
        $this->add(new Hidden('idIndividu'));
        $this->add((
        new Text('individu'))
            ->setLabel("Votre identité :")
            ->setAttribute('readonly','readonly')
        );
        $this->add((
        new Text('email'))
            ->setLabel("Votre adresse électronique :")
        );
        $this->add((
         new Submit('enregistrer'))
            ->setValue('Enregister')
            ->setAttribute('class','btn-info')
        );

        $this->setInputFilter((new Factory())->createInputFilter([
            'individu' => [
                'name' => 'individu',
                'required' => true,
            ],
            'email' => [
                'name' => 'email',
                'required' => true,
            ],
        ]));
    }


}