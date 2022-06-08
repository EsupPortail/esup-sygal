<?php

namespace UnicaenAvis\Form;

use InvalidArgumentException;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Radio;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\InArray;
use UnicaenAvis\Entity\Db\Avis;
use UnicaenAvis\Entity\Db\AvisType;
use UnicaenAvis\Entity\Db\AvisTypeValeurComplem;
use Webmozart\Assert\Assert;

class AvisForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var \UnicaenAvis\Entity\Db\Avis
     */
    protected $object;

    /**
     * Type de l'avis demandé.
     *
     * @var \UnicaenAvis\Entity\Db\AvisType
     */
    protected AvisType $avisType;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeur[]
     */
    protected array $avisTypeValeurs;

    /**
     * Valeurs d'avis possibles (avis positif, avis négatif, etc.)
     *
     * @var \UnicaenAvis\Entity\Db\AvisValeur[]
     */
    protected array $avisValeurs;

    /**
     * Elément de formualaire correspondant à la valeur de l'avis.
     *
     * @var \Laminas\Form\Element\Radio
     */
    private Radio $avisElement;

    /**
     * Eléments de formulaires correspondants aux éventuels compléments demandés.
     *
     * @var \Laminas\Form\ElementInterface[]
     */
    protected array $avisTypeValeurComplemsElements = [];

    /**
     * @var callable|null
     */
    private $avisTypeValeurComplemsFilter = null;

    /**
     * Spécifie le filtre à utiliser dans un {@see array_filter()} pour filtrer les éventuels compléments d'avis.
     *
     * @param callable $filter
     */
    public function setAvisTypeValeurComplemsFilter(callable $filter)
    {
        $this->avisTypeValeurComplemsFilter = $filter;
    }

    /**
     * @param Avis $object
     * @param int $flags
     * @return $this
     */
    public function bind($object, $flags = FormInterface::VALUES_NORMALIZED): self
    {
        Assert::isInstanceOf($object, Avis::class);

        $this->avisType = $object->getAvisType();
        $this->avisTypeValeurs = $this->avisType->getAvisTypeValeurs()->toArray();

        // collecte des valeurs d'avis possibles
        $this->avisValeurs = [];
        foreach ($this->avisTypeValeurs as $avisTypeValeur) {
            $avisValeur = $avisTypeValeur->getAvisValeur();
            $this->avisValeurs[$avisValeur->getCode()] = $avisValeur;
        }

        $this->updateElements();

        return parent::bind($object, $flags);
    }

    protected function updateElements()
    {
        // Création de l'élément correspondant à l'avis demandé
        $avisValueOptions = [];
        foreach ($this->avisValeurs as $avisValeur) {
            $avisValueOptions[$avisValeur->getCode()] = $avisValeur->getValeur();
        }

        /** @var Radio $avisRadio */
        $avisRadio = $this->getFormFactory()->createElement([
            'type' => Radio::class,
            'name' => $this->avisType->getCode(),
            'options' => [
                'order' => 10,
                'label' => $this->avisType->getLibelle(),
                'value_options' => $avisValueOptions,
                'attributes' => [
                    'class' => 'radio-inline',
                ],
            ],
        ]);
        $this->add($avisRadio);
        $this->avisElement = $avisRadio;

        // Création des éléments pour les éventuels compléments demandés
        foreach ($this->avisTypeValeurs as $avisTypeValeur) {
            $order = 100;
            $avisTypeValeurComplems = $avisTypeValeur->getAvisTypeValeurComplems()->toArray();
            if ($this->avisTypeValeurComplemsFilter !== null) {
                $avisTypeValeurComplems = array_filter($avisTypeValeurComplems, $this->avisTypeValeurComplemsFilter);
            }
            usort($avisTypeValeurComplems, [AvisTypeValeurComplem::class, 'sorterByOrdre']);
            foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
                $this->addElementsForAvisTypeValeurComplem($avisTypeValeurComplem, $order);
                $order++;
            }
        }
    }

    protected function addElementsForAvisTypeValeurComplem(AvisTypeValeurComplem $avisTypeValeurComplem, int $order)
    {
        $element = $this->createElementForAvisTypeValeurComplem($avisTypeValeurComplem, $order);
        $this->add($element);

        $this->avisTypeValeurComplemsElements[$element->getName()] = $element;
    }

    protected function createElementForAvisTypeValeurComplem(AvisTypeValeurComplem $avisTypeValeurComplem, int $order): ElementInterface
    {
        $name = $avisTypeValeurComplem->getCode();
        $label = $avisTypeValeurComplem->getLibelle();
        $class = $avisTypeValeurComplem->getAvisTypeValeur()->getAvisValeur()->getCode();

        // Gestion des compléments avec dépendance "parent/enfants" (UNIQUEMENT pour un parent de type checkbox)
        $childrenClass = 'children-of-' . $avisTypeValeurComplem->getCode();
        $isParent = count($avisTypeValeurComplem->getAvisTypeValeurComplemsEnfants()) > 0;
        $isChild = $avisTypeValeurComplem->getAvisTypeValeurComplemParent() !== null;
        if ($isChild) {
            $parentCode = $avisTypeValeurComplem->getAvisTypeValeurComplemParent()->getCode();
            $class .= " avis-type-complem-children children-of-$parentCode";
        }

        switch ($type = $avisTypeValeurComplem->getType()) {

            case AvisTypeValeurComplem::TYPE_COMPLEMENT_CHECKBOX:
                $element = $this->getFormFactory()->createElement([
                    'type' => Checkbox::class,
                    'name' => $name,
                    'options' => [
                        'order' => $order,
                        'label' => $label,
                        'label_options' => [
                            'disable_html_escape' => true,
                        ],
                        'label_attributes' => [
                            'class' => $class,
                        ],
                    ],
                    'attributes' => [
                        'data-children-class' => $isParent ? $childrenClass : '',
                        'class' => $class,
                    ],
                ]);
                break;

            case AvisTypeValeurComplem::TYPE_COMPLEMENT_TEXTAREA:
                $element = $this->getFormFactory()->createElement([
                    'type' => Textarea::class,
                    'name' => $name,
                    'options' => [
                        'order' => $order,
                        'label' => $label,
                        'label_attributes' => [
                            'class' => $class,
                        ],
                    ],
                    'attributes' => [
                        'class' => $class,
                    ],
                ]);
                break;

            case AvisTypeValeurComplem::TYPE_COMPLEMENT_INFORMATION:
                $element = $this->getFormFactory()->createElement([
                    'type' => Textarea::class,
                    'name' => $name,
                    'options' => [
                        'order' => $order,
                        'label' => sprintf('<i class="fas fa-info-circle"></i> %s', $label),
                        'label_options' => [
                            'disable_html_escape' => true,
                        ],
                        'label_attributes' => [
                            'class' => $class,
                        ],
                    ],
                    'attributes' => [
                        'class' => $class,
                        'style' => 'display: none'
                    ],
                ]);
                break;

            default:
                throw new InvalidArgumentException("Type de complément rencontré inattendu : " . $type);
        }

        return $element;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->add((new Submit('submit'))
            ->setValue("Enregister")
            ->setAttribute('class', 'btn btn-primary')
        );

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

        // Des éventuels compléments associés à l'avis sélectionné peuvent faire partie d'un ensemble dans lequel
        // l'un au moins doit être renseigné.
        $avisTypeValeurComplems = $this->getAvisTypeValeurComplemsForAvisValeurSelectionnee();
        $avisTypeValeurComplems = array_filter($avisTypeValeurComplems, fn(AvisTypeValeurComplem $atc) => $atc->isObligatoireUnAuMoins());
        if ($avisTypeValeurComplems) {
            if (!$this->atLeastOneAvisTypeValeurComplemRenseigne($avisTypeValeurComplems)) {
                $message = count($avisTypeValeurComplems) === 1 ?
                    "Vous devez renseigner ce complément" :
                    "Vous devez renseigner au moins l'un de ces compléments";
                foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
                    $name = $avisTypeValeurComplem->getCode();
                    $this->get($name)->setMessages([$message]);
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Teste si l'un au moins des compléments spécifiés est renseigné.
     *
     * @param array $avisTypeValeurComplems
     * @return bool
     */
    protected function atLeastOneAvisTypeValeurComplemRenseigne(array $avisTypeValeurComplems): bool
    {
        Assert::notEmpty($avisTypeValeurComplems);

        foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
            if ($this->data[$avisTypeValeurComplem->getCode()] ?? null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification(): array
    {
        $inputFilterSpecification = [
            'csrf' => [
                'required' => true,
            ],
        ];

        //
        // valeur de l'avis : obligatoire + liste de valeurs autorisées
        //
        $avisValeursPossibles = array_keys($this->avisValeurs);
        $inputFilterSpecification = array_merge($inputFilterSpecification, [
            $this->avisElement->getName() => [
                'required' => true,
                'validators' => [new InArray(['haystack' => $avisValeursPossibles])]
            ]
        ]);

        //
        // compléments de l'avis sélectionné : obligatoires ou facultatifs
        //
        $avisTypeValeurComplems = $this->getAvisTypeValeurComplemsForAvisValeurSelectionnee();
        foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
            $inputFilterSpecification = array_merge($inputFilterSpecification, [
                $avisTypeValeurComplem->getCode() => ['required' => $avisTypeValeurComplem->isObligatoire()]
            ]);
            // un complément enfant est obligatoire si le parent est coché
            if ($avisTypeValeurComplemParent = $avisTypeValeurComplem->getAvisTypeValeurComplemParent()) {
                $avisTypeValeurComplemParentValue = $this->data[$avisTypeValeurComplemParent->getCode()] ?? null;
                if ($avisTypeValeurComplemParentValue) {
                    $inputFilterSpecification = array_merge($inputFilterSpecification, [
                        $avisTypeValeurComplem->getCode() => ['required' => true]
                    ]);
                }
            }
        }

        //
        // compléments des avis non sélectionnés : forcément facultatifs
        //
        $avisTypeValeurComplems = $this->getAvisTypeValeurComplemsForAvisValeurNonSelectionnee();
        foreach ($avisTypeValeurComplems as $avisTypeValeurComplem) {
            $inputFilterSpecification = array_merge($inputFilterSpecification, [
                $avisTypeValeurComplem->getCode() => ['required' => false]
            ]);
        }

        return $inputFilterSpecification;
    }

    /**
     * Retourne les éventuels {@see AvisTypeValeurComplems} associés à la valeur d'avis sélectionnée.
     *
     * @return \UnicaenAvis\Entity\Db\AvisTypeValeurComplem[]
     */
    protected function getAvisTypeValeurComplemsForAvisValeurSelectionnee(): array
    {
        $avisValue = $this->data[$this->avisElement->getName()];

        foreach ($this->avisTypeValeurs as $avisTypeValeur) {
            if ($avisTypeValeur->getAvisValeur()->getCode() === $avisValue) {
                $avisTypeValeurComplems = $avisTypeValeur->getAvisTypeValeurComplems()->toArray();
                usort($avisTypeValeurComplems, [AvisTypeValeurComplem::class, 'sorterByOrdre']);

                return $avisTypeValeurComplems;
            }
        }

        return [];
    }

    /**
     * Retourne les éventuels {@see AvisTypeValeurComplems} associés aux valeurs d'avis non sélectionnées.
     *
     * @return \UnicaenAvis\Entity\Db\AvisTypeValeurComplem[]
     */
    protected function getAvisTypeValeurComplemsForAvisValeurNonSelectionnee(): array
    {
        $avisValue = $this->data[$this->avisElement->getName()];

        $avisTypeValeurComplems = [];
        foreach ($this->avisTypeValeurs as $avisTypeValeur) {
            if ($avisTypeValeur->getAvisValeur()->getCode() !== $avisValue) {
                $avisTypeValeurComplems = array_merge($avisTypeValeurComplems, $avisTypeValeur->getAvisTypeValeurComplems()->toArray());
            }
        }

        return $avisTypeValeurComplems;
    }

    /**
     * @return \Laminas\Form\Element\Radio
     */
    public function getAvisElement(): Radio
    {
        return $this->avisElement;
    }

    /**
     * @return \Laminas\Form\ElementInterface[]
     */
    public function getAvisTypeValeurComplemsElements(): array
    {
        return $this->avisTypeValeurComplemsElements;
    }
}