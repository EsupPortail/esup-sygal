<?php

namespace Application\Service\ListeDiffusion\Address;

use Application\Entity\Db\Role;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class ListeDiffusionAddressParser extends ListeDiffusionAbstractAddressParser
{
    const CODES_ROLES = [
        Role::CODE_ADMIN_TECH,
        Role::CODE_BDD,
        Role::CODE_BU,
    ];

    /**
     * Adresse complète de la liste de diffusion, ex :
     *   - ED591.doctorants.insa@normandie-univ.fr
     *   - ED591.doctorants@normandie-univ.fr
     *   - ED591.dirtheses@normandie-univ.fr
     *
     * Où :
     * - 'ED591' est le nom de l'école doctorale ;
     * - 'doctorants' (ou 'dirtheses') est un alias de rôle ;
     * - 'insa' est le code de l'établissement en minuscules.
     */
    protected string $address;

    protected ListeDiffusionAddressGenerator $listeDiffusionAddressGenerator;

    public function __construct()
    {
        $this->listeDiffusionAddressGenerator = new ListeDiffusionAddressGenerator();
    }

    public function getEcoleDoctoraleRegexp(): string
    {
        return sprintf('/^%s([0-9]+)$/', $this->listeDiffusionAddressGenerator->getEcoleDoctoralePrefix());
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function parse(): ListeDiffusionAddressParserResult
    {
        $adresseElements = explode('@', $this->address)[0]; // ex: 'ED591.doctorants.insa'
        $this->adressElements = explode($this->listeDiffusionAddressGenerator->getSeparator(), $adresseElements); // ex: ['ED591', 'doctorants', 'insa']

        $listeElements = $this->adressElements;
        $nbElements = count($this->adressElements);

        //
        // Adresse en 3 parties.
        // Ex: 'ED591.doctorants.ucn'
        //
        if ($nbElements === 3) {
            $structure = array_shift($listeElements); // ex: 'ED591'
            $role = array_shift($listeElements); // ex: 'doctorants', 'dirtheses', 'admin_tech'
            $etablissement = array_shift($listeElements); // ex: 'insa', 'ucn'

            if ($this->stringMatchesEcoleDoctorale($structure)) {
                $code = $this->stringExtractEcoleDoctorale($structure); // ex : '591'

                $result = new ListeDiffusionAddressParserResultWithED();
                $result->setEcoleDoctorale($code);
            } else {
                throw new InvalidArgumentException("Cas imprévu.");
            }
        }
        //
        // Adresse en 2 parties.
        // Ex: 'doctorants.ucn'
        // ou  'ED591.doctorants'
        //
        elseif ($nbElements === 2) {
            $premierElement = array_shift($listeElements);
            if ($this->stringMatchesEcoleDoctorale($premierElement)) {
                // Ex: 'ED591'
                $structure = $this->stringExtractEcoleDoctorale($premierElement); // ex : '591'
                $role = array_shift($listeElements);
                $etablissement = null;

                $result = new ListeDiffusionAddressParserResultWithED();
                $result->setEcoleDoctorale($structure);
            } else {
                // Ex: 'doctorants'
                $structure = null;
                $role = $premierElement;
                $etablissement = array_shift($listeElements);

                $result = new ListeDiffusionAddressParserResult();
            }
        }
        //
        // Adresse en 1 partie.
        // Ex: 'doctorants'
        //
        elseif ($nbElements === 1) {
            $structure = null;
            $role = array_shift($listeElements);
            $etablissement = null;

            $result = new ListeDiffusionAddressParserResult();
        }
        //
        // Autres cas : format inconnu.
        //
        else {
            throw new InvalidArgumentException("Format inconnu.");
        }

        if (! $this->stringMatchesRole($role)) {
            throw new InvalidArgumentException("Ceci ne correspond pas à un rôle supporté : " . $role);
        }

        $result->setRole($role);
        $result->setEtablissement($etablissement);

        return $result;
    }

    private function stringMatchesEcoleDoctorale(string $s): bool
    {
        return (bool) preg_match($this->getEcoleDoctoraleRegexp(), $s);
    }

    private function stringExtractEcoleDoctorale(string $s): string
    {
        Assert::true($this->stringMatchesEcoleDoctorale($s), "Extraction impossible !");

        preg_match($this->getEcoleDoctoraleRegexp(), $s, $matches);

        return $matches[1];
    }

    private function stringMatchesRole(string $s): bool
    {
        $allCodes = array_merge(
            self::CODES_ROLES,
            $this->listeDiffusionAddressGenerator->getCodesRolesAliases()
        );

        return in_array(
            strtoupper($s),
            array_map('strtoupper', $allCodes)
        );
    }
}