<?php

namespace Application\View\Helper;

use Application\Entity\Db\Financement;
use Application\Filter\FinancementFormatter;

class FinancementFormatterHelper extends \Laminas\View\Helper\AbstractHelper
{
    /**
     * @var FinancementFormatter
     */
    private $formatter;

    /**
     * @param FinancementFormatter $formatter
     * @return self
     */
    public function setFormatter(FinancementFormatter $formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * @param string $displayAs
     * @return self
     */
    public function setDisplayAs(string $displayAs): self
    {
        $this->formatter->setDisplayAs($displayAs);
        return $this;
    }

    /**
     * @param bool $displayComplement
     * @return self
     */
    public function setDisplayComplement(bool $displayComplement): self
    {
        $this->formatter->setDisplayComplement($displayComplement);
        return $this;
    }

    /**
     * @param string $sortBy
     * @return self
     */
    public function setSortBy(string $sortBy): self
    {
        $this->formatter->setSortBy($sortBy);
        return $this;
    }

    /**
     * @param Financement[] $financements
     * @return array|string
     */
    public function format(array $financements): array|string
    {
        return $this->formatter->doFormat($financements);
    }
}