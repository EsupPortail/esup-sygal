<?php

namespace Application\Service\ListeDiffusion\Address;

use Application\Entity\Db\Role;
use InvalidArgumentException;

class ListeDiffusionAddressParser extends ListeDiffusionAbstractAddressParser
{
    const REGEXP_ECOLE_DOCTORALE = '/^(ED)[a-zA-Z0-9-]+$/';
    const REGEXP_UNITE_RECHERCHE = '/^(UR)[a-zA-Z0-9-]+$/';

    const CODES_ROLES = [
        Role::CODE_ADMIN_TECH,
        Role::CODE_BDD,
        Role::CODE_BU,
    ];

    /**
     * Adresse complète de la liste de diffusion, ex :
     *   - ED591NBISE.doctorants.insa@normandie-univ.fr
     *   - ED591NBISE.doctorants@normandie-univ.fr
     *   - ED591NBISE.dirtheses@normandie-univ.fr
     *
     * Où :
     * - 'ED591NBISE' est le nom de l'école doctorale ;
     * - 'doctorants' (ou 'dirtheses') est un alias de rôle ;
     * - 'insa' est le code de l'établissement en minuscules.
     *
     * @var string
     */
    protected $address;

    /**
     * @var string[]
     */
    protected $adresseElements;

    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return ListeDiffusionAddressParserResult
     */
    public function parse()
    {
        $this->adresseElements = explode('@', $this->address)[0]; // ex: 'ED591NBISE.doctorants.insa'
        $this->adresseElements = explode(ListeDiffusionAddressGenerator::SEPARATOR, $this->adresseElements); // ex: ['ED591NBISE', 'doctorants', 'insa']

        $listeElements = $this->adresseElements;
        $nbElements = count($this->adresseElements);

        //
        // Adresse en 3 parties.
        // Ex: 'ED591NBISE.doctorants.ucn'
        //
        if ($nbElements === 3) {
            $structure = array_shift($listeElements); // ex: 'ED591NBISE'
            $role = array_shift($listeElements); // ex: 'doctorants', 'dirtheses', 'admin_tech'
            $etablissement = array_shift($listeElements); // ex: 'insa', 'ucn'

            if ($this->stringMatchesEcoleDoctorale($structure)) {
                $result = new ListeDiffusionAddressParserResultWithED();
                $result->setEcoleDoctorale($structure);
            } elseif ($this->stringMatchesUniteRecherche($structure)) {
                throw new InvalidArgumentException("Non implémenté.");
            } else {
                throw new InvalidArgumentException("Cas imprévu.");
            }
        }
        //
        // Adresse en 2 parties.
        // Ex: 'doctorants.ucn'
        // ou  'ED591NBISE.doctorants'
        //
        elseif ($nbElements === 2) {
            $premierElement = array_shift($listeElements);
            if ($this->stringMatchesEcoleDoctorale($premierElement)) {
                // Ex: 'ED591NBISE.doctorants'
                $structure = $premierElement;
                $role = array_shift($listeElements);
                $etablissement = null;

                $result = new ListeDiffusionAddressParserResultWithED();
                $result->setEcoleDoctorale($structure);
            } else {
                // Ex: 'doctorants.ucn'
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

    private function stringMatchesEcoleDoctorale(string $s)
    {
        return preg_match(self::REGEXP_ECOLE_DOCTORALE, $s);
    }

    private function stringMatchesUniteRecherche(string $s)
    {
        return preg_match(self::REGEXP_UNITE_RECHERCHE, $s);
    }

    private function stringMatchesRole(string $s)
    {
        $allCodes = array_merge(
            self::CODES_ROLES,
            ListeDiffusionAddressGenerator::CODES_ROLES_ALIASES
        );

        return in_array(
            strtoupper($s),
            array_map('strtoupper', $allCodes)
        );
    }
}