<?php

namespace Admission\Form\Transmission;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class TransmissionForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $this->add(
            (new Text('codeVoeu'))
                ->setLabel("Code voeu <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(
            (new Text('codePeriode'))
                ->setLabel("Code période <span class='icon icon-obligatoire' style='color: darkred;font-size: 0.8em;' data-bs-toggle='tooltip' title='Obligatoire'></span> :")
                ->setLabelOptions(['disable_html_escape' => true,])
                ->setAttributes( ['class' => 'form-control'])
        );

        $this->add(new Csrf('security'), ['csrf_options' => ['timeout' => 60]]);

        FormUtils::addSaveButton($this);
    }

    /**
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'codeVoeu' => [
                'name' => 'codeVoeu',
                'required' => true,
            ],
            'codePeriode' => [
                'name' => 'codePeriode',
                'required' => true,
            ]
        ];
    }
}