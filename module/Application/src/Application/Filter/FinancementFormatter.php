<?php

namespace Application\Filter;

use Application\Entity\Db\Financement;
use Application\Entity\Db\OrigineFinancement;
use Application\Provider\Privilege\FinancementPrivileges;
use Application\Service\AuthorizeServiceAwareTrait;
use InvalidArgumentException;
use UnicaenApp\Exception\LogicException;
use UnicaenAuth\Service\AuthorizeService;
use Webmozart\Assert\Assert;

class FinancementFormatter
{
    use AuthorizeServiceAwareTrait;

    const DISPLAY_AS_HTML_LINES = 'DISPLAY_AS_HTML_LINES';
    const DISPLAY_AS_ARRAY = 'DISPLAY_AS_ARRAY';

    const SORT_BY_DATE = 'SORT_DATE';
    const SORT_BY_ORIGINE = 'SORT_ORIGINE';

    private string $displayAs = self::DISPLAY_AS_HTML_LINES;
    private string $sortBy = self::SORT_BY_DATE;
    private bool $displayComplement = false;

    /**
     * @param string $displayAs
     * @return self
     */
    public function setDisplayAs(string $displayAs): self
    {
        Assert::inArray($displayAs, [
            self::DISPLAY_AS_HTML_LINES,
            self::DISPLAY_AS_ARRAY,
        ]);
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
     * @return array|string
     */
    public function doFormat(array $financements): array|string
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

        switch ($this->displayAs) {
            case FinancementFormatter::DISPLAY_AS_HTML_LINES :
                $output = $this->doFormatSeparated($financements);
                break;
            case FinancementFormatter::DISPLAY_AS_ARRAY :
                $output = $this->doFormatArray($financements);
                break;
            default:
                throw new InvalidArgumentException("Option d'affichage imprévue");
        }

        return $output;
    }

    /**
     * This function format an array of acteurs as Separated Values object
     *
     * @param  Financement[] $financements
     * @return string Separated Values object
     */
    private function doFormatSeparated(array $financements): string
    {
        $data = [];
        foreach ($financements as $financement) {
            $infos = [];
            if ($financement->getAnnee()) {
                $infos[] = $financement->getAnnee();
            }
            if ($origine = $financement->getOrigineFinancement()) {
                if ($this->isOrigineVisible($origine)) {
                    $infos[] = 'Origine : ' . $origine->getLibelleLong();
                }
            }
            if ($this->displayComplement && $financement->getComplementFinancement()) {
                $infos[] = "Complément : " . $financement->getComplementFinancement();
            }
            if ($financement->getQuotiteFinancement()) {
                $infos[] = 'Quotité : ' . $financement->getQuotiteFinancement();
            }
            $infos[] = $this->formatDates($financement);
            $infos[] = $this->formatTypeFinancement($financement);

            $data[] = array_filter($infos);
        }

        $output = '';
        foreach ($data as $infos) {
            $line = implode(" ; ", $infos);
            $output .= $line . "<br/>";
        }
        return $output;
    }

    /**
     * This function format an array of financements as Array.
     *
     * @param Financement[] $financements
     * @return array Array of array with key => value
     */
    private function doFormatArray(array $financements): array
    {
        $infos = [];

        foreach ($financements as $financement) {
            $info = [];
            if ($financement->getAnnee()) {
                if($financement->getDateDebut() && $financement->getDateFin()  && $financement->getDateFin()){
                    $annee = $financement->getDateDebut()->format("Y")."/".$financement->getDateFin()->format("Y");
                }else{
                    $annee = $financement->getAnnee();
                }
                $info["annee"] = $annee;
            }
            if ($origine = $financement->getOrigineFinancement()) {
                if ($this->isOrigineVisible($origine)) {
                    $info["origine"] = $origine->getLibelleLong();
                }
            }
            if ($this->displayComplement && $financement->getComplementFinancement()) {
                $info["complement"] = $financement->getComplementFinancement();
            }
            if ($financement->getQuotiteFinancement()) {
                $info["quotite"] = $financement->getQuotiteFinancement();
            }
            $info["dates"] = $this->formatDates($financement);
            $info["typeFinancement"] = $this->formatTypeFinancement($financement);

            $infos[] = $info;
        }
        return $infos;
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

        return sprintf("Type de financement = %s (%s)", $financement->getLibelleTypeFinancement(), $financement->getCodeTypeFinancement());
    }

    private function formatDates(Financement $financement): ?string
    {
        if ($financement->getDateDebut() && $financement->getDateFin()) {
            return 'Du ' . $financement->getDateDebut()->format('d/m/Y') . ' au ' . $financement->getDateFin()->format('d/m/Y');
        }
        if ($financement->getDateDebut()) {
            return 'À partir du ' . $financement->getDateDebut()->format('d/m/Y');
        }
        if ($financement->getDateFin()) {
            return "Jusqu'au " . $financement->getDateFin()->format('d/m/Y');
        }
        return null;
    }
}