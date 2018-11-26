<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Filter\EtablissementPrefixFilter;
use stdClass;

class JSONExtractor
{
    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @param string $propertyName
     * @param stdClass $jsonEntity
     * @return string|null
     */
    public function extractPropertyValue($propertyName, stdClass $jsonEntity)
    {
        $codeEtablissement = $this->etablissement->getStructure()->getCode();

        $prefix = null;
        $value = null;

        switch ($propertyName) {
            case 'etablissementId': // UCN, URN, ULHN ou INSA, sans préfixage
                $value = $codeEtablissement;
                break;
            case 'sourceCode':
                $prefix = $codeEtablissement;
                $value = $jsonEntity->id;
                break;
            case 'sourceId':
            case 'individuId':
            case 'roleId':
            case 'theseId':
            case 'doctorantId':
            case 'structureId':
            case 'ecoleDoctId':
            case 'uniteRechId':
            case 'acteurEtablissementId':
                $prefix = $codeEtablissement;
                $value = isset($jsonEntity->{$propertyName}) ? $jsonEntity->{$propertyName} : null;
                break;
            case 'origineFinancementId':
                $prefix = Etablissement::CODE_STRUCTURE_COMUE; // particularité!
                $value = isset($jsonEntity->{$propertyName}) ? $jsonEntity->{$propertyName} : null;
                break;
            default:
                $value = isset($jsonEntity->{$propertyName}) ? $jsonEntity->{$propertyName} : null;
                break;
        }

        // préfixage éventuel
        $f = new EtablissementPrefixFilter();
        if ($value !== null && $prefix !== null) {
            $value = $f->addPrefixTo($value, $prefix);
        }

        return $value;
    }
}