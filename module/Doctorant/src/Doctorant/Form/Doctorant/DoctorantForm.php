<?php

namespace Doctorant\Form\Doctorant;

use Application\Utils\FormUtils;
use Doctorant\Entity\Db\Doctorant;
use Laminas\Filter\StringTrim;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 * @method Doctorant getObject()
 * @method Doctorant getData()
 */
class DoctorantForm extends Form implements InputFilterProviderInterface
{
    public function init(): void
    {
        $this->add(
            new Hidden('id')
        );
        $this->add(
            (new Text('ine'))
                ->setLabel("INE :")
        );
        $this->add(
            (new Text('orcid'))
                ->setLabel("ORCID :")
        );
        $this->add(
            (new Text('idRef'))
                ->setLabel("IdRef :")
        );
        $this->add(
            (new Text('codeApprenantInSource'))
                ->setLabel("Code apprenant :")
        );
        $this->add(
            new Csrf('csrf')
        );

        FormUtils::addSaveButton($this);
    }

    public function prepare(): self
    {
        $estModifiable = ! $this->isSourceImportable();

        $this->get('ine')->setAttribute('disabled', !$estModifiable);
        $this->get('codeApprenantInSource')->setAttribute('disabled', !$estModifiable);

        return parent::prepare();

    }

    protected function isSourceImportable(): bool
    {
        return $this->getObject()->getSource()?->getImportable();

    }

    public function getInputFilterSpecification(): array
    {
        $estModifiable = ! $this->isSourceImportable();

        return [
            'ine' => [
                'required' => $estModifiable,
                'filters' => [
                    ['name' => StringTrim::class],
                ],
            ],
            'idRef' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => ToNull::class],
                ],
            ],
            'codeApprenantInSource' => [
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => ToNull::class],
                ],
            ],
        ];
    }
}