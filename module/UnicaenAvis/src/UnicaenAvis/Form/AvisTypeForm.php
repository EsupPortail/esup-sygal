<?php

namespace UnicaenAvis\Form;

use Application\Utils\FormUtils;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Fieldset\AvisTypeFieldset;
use Webmozart\Assert\Assert;

class AvisTypeForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var \UnicaenAvis\Entity\Db\AvisType
     */
    protected $object;

    /**
     * @param AvisType $object
     * @param int $flags
     * @return $this
     */
    public function bind($object, $flags = FormInterface::VALUES_NORMALIZED): self
    {
        Assert::isInstanceOf($object, AvisType::class);

        return parent::bind($object, $flags);
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

        /** @var \UnicaenAvis\Fieldset\AvisTypeFieldset $avisTypeFieldset */
        $avisTypeFieldset = $this->getFormFactory()->getFormElementManager()->get(AvisTypeFieldset::class);
        $avisTypeFieldset->setUseAsBaseFieldset(true);
        $this->add($avisTypeFieldset, ['name' => 'avisTypeFieldset']);

        FormUtils::addSaveButton($this);

        $this->add(new Csrf('csrf'));
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        if (!parent::isValid()) {
            return false;
        }


        return true;
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'csrf' => [
                'required' => true,
            ],
        ];
    }
}