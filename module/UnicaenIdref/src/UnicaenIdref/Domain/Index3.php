<?php

namespace UnicaenIdref\Domain;

class Index3 extends AbstractIndex
{
    protected string $name = 'Index3';
    protected string $valueName = 'Index3Value';

    protected const INDEX_Langues = 'Langues';

    /**
     * Les valeurs possibles sont les codes de langue ISO 639-2 (sur trois caractères).
     *
     * @param string $indexValue ex: 'fre'
     * @return $this
     */
    public function setLangues(string $indexValue): self
    {
        return $this
            ->setIndex(self::INDEX_Langues)
            ->setIndexValue($indexValue);
    }
}