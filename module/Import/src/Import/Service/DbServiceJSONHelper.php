<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\SourceCodeStringHelperAwareTrait;
use stdClass;

class DbServiceJSONHelper
{
    use SourceCodeStringHelperAwareTrait;

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

        $value = null;

        switch ($propertyName) {
            case 'etablissementId': // UCN, URN, ULHN ou INSA, sans préfixage
                $prefixRequired = false;
                $prefix = null;
                $value = $codeEtablissement;
                break;
            case 'sourceCode':
                $prefixRequired = true;
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
                $prefixRequired = true;
                $prefix = $codeEtablissement;
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
            case 'origineFinancementId':
                $prefixRequired = true;
                $prefix = null; // particularité! prefix par défaut.
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
            default:
                $prefixRequired = false;
                $prefix = null;
                $value = isset($jsonObject->{$propertyName}) ? $jsonObject->{$propertyName} : null;
                break;
        }

        // préfixage éventuel
        if ($value !== null && $prefixRequired) {
            if ($prefix === null) {
                $value = $this->sourceCodeStringHelper->addDefaultPrefixTo($value);
            } else {
                $value = $this->sourceCodeStringHelper->addPrefixTo($value, $prefix);
            }
        }

        return $value;
    }
}