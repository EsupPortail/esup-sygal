<?php

namespace Application\Service\ListeDiffusion\Address;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Application\Entity\Db\Structure;
use Webmozart\Assert\Assert;

class ListeDiffusionAddressGenerator
{
    const SEPARATOR = '.';
    const CODES_ROLES_ALIASES = [
        Role::CODE_DOCTORANT => 'doctorants',
        Role::CODE_DIRECTEUR_THESE => 'dirtheses',
    ];

    /**
     * Domaine de l'adresse, ex: 'normandie-univ.fr'.
     *
     * @var string
     */
    protected $domain;

    /**
     * ED concernée éventuelle.
     *
     * @var EcoleDoctorale|null
     */
    protected $ecoleDoctorale;

    /**
     * Role concerné.
     *
     * @var Role
     */
    protected $role;

    /**
     * Etablissement concerné éventuel.
     *
     * @var Etablissement|null
     */
    protected $etablissement;

    /**
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param EcoleDoctorale|null $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale = null): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
        return $this;
    }

    /**
     * @param Role $role
     * @return self
     */
    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement = null): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    /**
     * @param Structure|null $structure
     * @return self
     */
    public function setEtablissementAsStructure(Structure $structure = null): self
    {
        if ($structure === null) {
            return $this->setEtablissement(null);
        }

        return $this->setEtablissement($structure->getEtablissement());
    }

    private function validateParams()
    {
        Assert::notNull($this->role, "Aucun rôle fourni");
        Assert::notNull($this->domain, "Aucun domaine fourni");
    }

    public function generateName()
    {
        $this->validateParams();

        $parts = [];
        $parts[] = $this->generateEcoleDoctoralePiece();
        $parts[] = $this->generateRolePiece();
        $parts[] = $this->generateEtablissementPiece();
        $parts = array_filter($parts);

        return implode(self::SEPARATOR, $parts) . '@' . $this->domain;
    }

    /**
     * @return string|null
     */
    private function generateEcoleDoctoralePiece()
    {
        if ($this->ecoleDoctorale === null) {
            return null;
        }
        if ($this->ecoleDoctorale->estTouteEcoleDoctoraleConfondue()) {
            return null;
        }

        Assert::notEmpty($this->ecoleDoctorale->getSigle(),
            "Ecole doctorale n°{$this->ecoleDoctorale->getId()} sans sigle");

        return trim(str_replace(str_split(' -_'), '', $this->ecoleDoctorale->getSigle())); // ex: 'ED590MIIS'
    }

    /**
     * @return string
     */
    private function generateRolePiece()
    {
        $code = $this->role->getCode();

        return self::CODES_ROLES_ALIASES[$code] ?? strtolower(str_replace('_-', '', $code));
    }

    /**
     * @return string|null
     */
    private function generateEtablissementPiece()
    {
        if ($this->etablissement === null) {
            return null;
        }

        return strtolower($this->etablissement->getStructure()->getCode()); // ex: 'UCN'
    }
}