<?php

namespace Application\Filter;

use Application\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
use Application\Provider\Privilege\FinancementPrivileges;
use Application\Service\AuthorizeServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenAuth\Service\AuthorizeService;

class FinancementFormatter
{
    use AuthorizeServiceAwareTrait;

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
     * @param string $displayAs
     * @return self
     */
    public function setDisplayAs(string $displayAs): self
    {
        $this->displayAs = $displayAs;
        return $this;
    }

    /**
     * @param string $sortBy
     * @return self
     */
    public function setSortBy(string $sortBy): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * @param Financement[] $financements
     * @return string
     */
    public function doFormat(array $financements): string
    {
        //sorting
        switch($this->sortBy) {
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
            switch($this->displayAs) {
                case FinancementFormatter::DISPLAY_AS_LINE :
                    $infos = [];
                    if ($financement->getAnnee())                   $infos[] = $financement->getAnnee();
                    if ($origine = $financement->getOrigineFinancement()) {
                        if ($this->isOrigineVisible($origine)) {
                            $infos[] = $origine->getLibelleLong();
                        }
                    }
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

    private function isOrigineVisible(OrigineFinancement $origine): bool
    {
        if ($this->authorizeService === null) {
            throw new LogicException(
                'Vous devez injecter dans ce formatter une instance du service ' . AuthorizeService::class . '. ' .
                'NB : si vous êtes dans une vue, utilisez $this->financementFormatter().'
            );
        }

        return $origine->isVisible() ||
            $this->authorizeService->isAllowed($origine, FinancementPrivileges::FINANCEMENT_VOIR_ORIGINE_NON_VISIBLE);
    }

    /**
     * @param Financement $financement
     * @return string|null
     */
    private function formatTypeFinancement(Financement $financement): ?string
    {
        if (! $financement->getLibelleTypeFinancement()) {
            return null;
        }

        return sprintf("%s (%s)", $financement->getLibelleTypeFinancement(), $financement->getCodeTypeFinancement());
    }
}