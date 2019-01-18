<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use stdClass;

class DbServiceJSONHelper
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
     * @param string   $propertyName
     * @param stdClass $jsonObject
     * @return string|null
     */
    public function extractPropertyValue($propertyName, stdClass $jsonObject)
    {
        $codeEtablissement = $this->etablissement->getCode();

        $prefix = null;
        $value = null;

        switch ($propertyName) {
            case 'etablissementId': // UCN, URN, ULHN ou INSA, sans préfixage
                $value = $codeEtablissement;
                break;
            case 'sourceCode':
                $prefix = $codeEtablissement;
                $value = $jsonObject->id;
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
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
            case 'origineFinancementId':
                $prefix = Etablissement::CODE_STRUCTURE_COMUE; // particularité!
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
            default:
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
        }

        // préfixage éventuel
        $sourceCodeHelper = new SourceCodeStringHelper();
        if ($value !== null && $prefix !== null) {
            $value = $sourceCodeHelper->addPrefixTo($value, $prefix);
        }

        return $value;
    }
}