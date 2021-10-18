<?php

namespace Application\Form;

use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Application\Entity\Db\MailConfirmation;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\InputFilter\Factory;

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
            ->setLabel("Individu :")
            ->setAttribute('readonly','readonly')
        );
        $this->add((
        new Text('email'))
            ->setLabel("Adresse électronique :")
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