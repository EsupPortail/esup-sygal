<?php

namespace Application\View\Helper;

use Application\Entity\Db\DomaineScientifique;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\StructureConcreteInterface;

/**
 * @deprecated formatteur trop fourre tout
 */
class SelectHelper  extends AbstractHelper {

    /** @var Etablissement[]|DomaineScientifique[] */
    private $listing;

    /** @var string */
    private $elementId;

    /** @var string */
    private $text;


    /**
     * SelectHelper constructor.
     * @param string $elementId
     * @param array $listing
     * @param string $text
     */
    public function __construct($elementId, $listing, $text)
    {
        $this->setElementId($elementId);
        $this->setListing($listing);
        $this->setText($text);
    }


    /**
     * @return array
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param array $listing
     * @return SelectHelper
     */
    public function setListing($listing)
    {
        $this->listing = $listing;
        return $this;
    }

    /**
     * @return string
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param string $elementId
     * @return SelectHelper
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return SelectHelper
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }


    public function render()
    {
        $texte  = '';
        $texte .= '<select id="'.$this->elementId.'">';
        $texte .= '<option>'.$this->text.'</option>';
        foreach ($this->listing as $element) {
            if ($element instanceof StructureConcreteInterface) {
                $libelle = $element->getStructure()->getLibelle();
            } else {
                $libelle = $element->getLibelle();
            }
            $texte .= '<option value="'.$element->getId().'">'.$libelle.'</option>';
        }
        $texte .= '</select>';
        return $texte;
    }

    public function asDataArray() {
        $data = [];

        $data[] = [
            'value' => '-1',
            'label' => $this->getText(),
        ];

        foreach ($this->listing as $element) {
            if ($element instanceof StructureConcreteInterface) {
                $libelle = $element->getStructure()->getLibelle();
            } else {
                $libelle = $element->getLibelle();
            }

            $data[] = [
                'value' => $element->getId(),
                'label' => $libelle,
            ];
        }
        return $data;
    }
}