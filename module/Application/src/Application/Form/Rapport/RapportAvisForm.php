<?php

namespace Application\Form\Rapport;

use Application\Entity\Db\RapportAvis;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;

class RapportAvisForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var string[]
     */
    protected $avisPossibles;

    /**
     * @param string[] $avisPossibles
     * @return self
     */
    public function setAvisPossibles(array $avisPossibles): self
    {
        $this->avisPossibles = $avisPossibles;

        return $this;
    }

    protected function getAvisPossiblesAsOptions(): array
    {
        return array_combine($this->avisPossibles, $this->avisPossibles);
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->setAttribute('method', 'post');

        $this->add([
            'type' => Hidden::class,
            'name' => 'id',
        ]);

        $factory = $this->getFormFactory();
        $this->add($factory->create([
            'type' => Radio::class,
            'name' => 'avis',
            'options' => [
                'label' => 'Avis :',
                'label_attributes' => [
                    'class' => 'required',
                ],
                'empty_option' => "Sélectionner...",
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'id' => 'avis',
                'class' => 'form-control',
            ],
        ]));

        $this->add([
            'name' => 'commentaires',
            'type' => Textarea::class,
            'options' => [
                'label' => "Commentaires :",
            ],
            'attributes' => [
                'id' => 'commentaires',
            ],
        ]);

        $this->add(new Csrf('security'));

        $this->add([
            'type' => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Enregistrer',
            ],
        ]);

        $this->bind(new RapportAvis());
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $this->prepareAvisRadio();

        return parent::prepare();
    }

    protected function prepareAvisRadio()
    {
        /** @var Radio $avisRadio */
        $avisRadio = $this->get('avis');
        $avisRadio->setValueOptions($this->getAvisPossiblesAsOptions());
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'avis' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => "Veuillez renseigner l'avis",
                            ],
                        ],
                    ],
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => array_keys($this->getAvisPossiblesAsOptions()),
                            'messages' => [
                                InArray::NOT_IN_ARRAY => "L'avis n'est pas dans la liste proposée",
                            ],
                        ],
                    ],
                ],
            ],

            'commentaires'  => [
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => "Veuillez renseigner les commentaires",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}