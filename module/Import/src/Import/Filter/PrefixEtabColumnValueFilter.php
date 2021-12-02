<?php

namespace Import\Filter;

use Application\Service\Source\SourceServiceAwareTrait;
use UnicaenDbImport\Domain\Source;
use UnicaenDbImport\Filter\ColumnValue\ColumnValueFilterInterface;

/**
 * Filtre utilisé lors de l'import pour préfixer certaines valeurs de colonnes destination par le code établissement,
 * ex : 'UMR6211' devient 'UCN::UMR6211'.
 */
class PrefixEtabColumnValueFilter implements ColumnValueFilterInterface
{
    use SourceServiceAwareTrait;

    /**
     * @var string
     */
    protected $codeEtablissement;

    /**
     * @var \UnicaenDbImport\Domain\Source
     */
    protected $source;

    /**
     * @inheritDoc
     */
    public function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function filter(string $name, $value)
    {
        if ($value === null) {
            return null;
        }

        switch ($name) {
            case 'SOURCE_ID':
            case 'INDIVIDU_ID':
            case 'ROLE_ID':
            case 'THESE_ID':
            case 'DOCTORANT_ID':
            case 'STRUCTURE_ID':
            case 'ECOLE_DOCT_ID':
            case 'UNITE_RECH_ID':
            case 'ACTEUR_ETABLISSEMENT_ID':
            case 'ORIGINE_FINANCEMENT_ID':
                $value = $this->getCodeEtablissement() . '::' . $value;
                break;
            default:
                break;
        }

        return $value;
    }

    private function getCodeEtablissement(): string
    {
        if ($this->codeEtablissement === null) {
            /** @var \Application\Entity\Db\Source $source */
            $source = $this->sourceService->getRepository()->findOneBy(['code' => $this->source->getName()]);
            $this->codeEtablissement = $source->getEtablissement()->getCode();
        }

        return $this->codeEtablissement;
    }
}