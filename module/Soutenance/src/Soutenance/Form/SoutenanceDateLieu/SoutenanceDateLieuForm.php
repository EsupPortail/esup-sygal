<?php

namespace Soutenance\Form\SoutenanceDateLieu;

use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Form;

class SoutenanceDateLieuForm extends Form {

    public function init()
    {
        $this->add(
            (new Text('date'))
                ->setLabel("Date de la soutenance :")
        );

        $this->add(
            (new Text('heure'))
                ->setLabel("Heure de la soutance :")
        );

        $this->add(
            (new Text('lieu'))
                ->setLabel("Lieu de la soutenance :")
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