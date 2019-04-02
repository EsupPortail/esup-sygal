<?php

namespace Soutenance\Form\DateLieu;

use UnicaenApp\Form\Element\Date;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Time;
use Zend\Form\Form;

class DateLieuForm extends Form {

    public function init()
    {
        $this->add(
            (new Date('date'))
                ->setLabel("Date de la soutenance :")
        );

        $this->add(
            (new Time('heure'))
                ->setFormat("24h")
                ->setLabel("Heure de la soutance :")

        );

        $this->add(
            (new Text('lieu'))
                ->setLabel("Lieu de la soutenance :")
        );
        $this->add(
            (new Checkbox('exterieur'))
                ->setLabel("Thèse soutenue à l'extérieur de l'établissement d'encadrement")
        );

        $this->add((new Submit('submit'))
            ->setValue("Enregistrer")
            ->setAttribute('class', 'btn btn-primary')
        );

//        $this->setInputFilter(
//            $this->getInputFilter()
//        );
    }

//    public function getInputFilterSpecification()
//    {
//        return [
//            'date' => [
//                'name' => 'date',
//                'required' => true,
//            ],
//            'heure' => [
//                'name' => 'heure',
//                'required' => true,
//            ],
//            'lieu' => [
//                'name' => 'lieu',
//                'required' => true,
//            ],
//        ];
//    }
}