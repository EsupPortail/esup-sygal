<?php

namespace Application\Filter;

use Application\Entity\Db\Financement;

class FinancementFormatter {

    const DISPLAY_AS_LINE = 'DISPLAY_LINE';

    const SORT_BY_DATE = 'SORT_DATE';
    const SORT_BY_ORIGINE = 'SORT_ORIGINE';

    /** @var string */
    private $displayAs;
    /** @var string */
    private $sortBy;
    /** @var boolean */
    private $displayComplement;

    /**
     * @param bool $displayComplement
     * @return FinancementFormatter
     */
    public function setDisplayComplement(bool $displayComplement)
    {
        $this->displayComplement = $displayComplement;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayAs()
    {
        return $this->displayAs;
    }

    /**
     * @param string $displayAs
     * @return FinancementFormatter
     */
    public function setDisplayAs($displayAs)
    {
        $this->displayAs = $displayAs;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param string $sortBy
     * @return FinancementFormatter
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * @param Financement[] $financements
     * @return string
     */
    public function doFormat(array $financements) {

        //sorting
        switch($this->getSortBy()) {
            case FinancementFormatter::SORT_BY_DATE :
                usort($financements, function (Financement $a, Financement $b) {
                    return $a->getAnnee() - $b->getAnnee();
                });
                break;
            case FinancementFormatter::SORT_BY_ORIGINE :
                usort($financements, function (Financement $a, Financement $b) {
                    return strcmp($a->getOrigineFinancement()->getLibelleLong(), $b->getOrigineFinancement()->getLibelleLong());
                });
                break;
        }

        $output = "";
        foreach ($financements as $financement) {
            switch($this->getDisplayAs()) {
                case FinancementFormatter::DISPLAY_AS_LINE :
                    $infos = [];
                    if ($financement->getAnnee())                   $infos[] = $financement->getAnnee();
                    if ($financement->getOrigineFinancement())      $infos[] = $financement->getOrigineFinancement()->getLibelleLong();
                    if ($this->displayComplement === true AND $financement->getComplementFinancement())   $infos[] = $financement->getComplementFinancement();
                    if ($financement->getQuotiteFinancement())      $infos[] = $financement->getQuotiteFinancement();
                    if ($financement->getDateDebut())               $infos[] = $financement->getDateDebut()->format('d/m/Y');
                    if ($financement->getDateFin())                 $infos[] = $financement->getDateFin()->format('d/m/Y');
                    $infos[] = $this->formatTypeFinancement($financement);
                    $line = implode(", ", array_filter($infos));
                    $output .= $line . "<br/>";
                    break;
            }
        }
        return $output;
    }

    /**
     * @param Financement $financement
     * @return string|null
     */
    public function formatTypeFinancement(Financement $financement)
    {
        if (! $financement->getLibelleTypeFinancement()) {
            return null;
        }

        return sprintf("%s (%s)", $financement->getLibelleTypeFinancement(), $financement->getCodeTypeFinancement());
    }
}