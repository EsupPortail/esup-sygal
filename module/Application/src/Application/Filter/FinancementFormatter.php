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
    public function doFormat($financements) {

        //sorting
        switch($this->getSortBy()) {
            case FinancementFormatter::SORT_BY_DATE :
                usort($financements->toArray(), function (Financement $a, Financement $b) { return $a->getDateDebut() > $b->getDateDebut();});
                break;
            case FinancementFormatter::SORT_BY_ORIGINE :
                usort($financements->toArray(), function (Financement $a, Financement $b) { return $a->getOrigineFinancement()->getLibelle() < $b->getOrigineFinancement()->getLibelle();});
                break;
        }

        $output = "";
        foreach ($financements as $financement) {
            switch($this->getDisplayAs()) {
                case FinancementFormatter::DISPLAY_AS_LINE :
                    $infos = [];
                    if ($financement->getOrigineFinancement())  $infos[] = $financement->getOrigineFinancement()->getLibelle();
                    if ($financement->getQuotiteFinancement())  $infos[] = $financement->getQuotiteFinancement();
                    if ($financement->getDateDebut())           $infos[] = $financement->getDateDebut()->format('d/m/Y');
                    if ($financement->getDateFin())             $infos[] = $financement->getDateFin()->format('d/m/Y');
                    $line = implode(", ", $infos);
                    $output .= $line . "<br/>";
                    break;
            }
        }
        return $output;
    }
}