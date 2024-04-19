<?php

namespace Application\Service\ListeDiffusion\Address;

use Application\Entity\Db\Role;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Structure\Entity\Db\Structure;
use Webmozart\Assert\Assert;

class ListeDiffusionAddressGenerator
{
    private const SEPARATOR = '.';
    private const ECOLE_DOCTORALE_PREFIX = 'ED';
    private const CODES_ROLES_ALIASES = [
        Role::CODE_DOCTORANT => 'doctorants',
        Role::CODE_DIRECTEUR_THESE => 'dirtheses',
    ];

    protected string $separator = self::SEPARATOR;

    /**
     * Prefixe utilisé dans le nommage d'une liste faisant référence à une ED.
     */
    protected string $ecoleDoctoralePrefix = self::ECOLE_DOCTORALE_PREFIX;

    protected array $codesRolesAliases = self::CODES_ROLES_ALIASES;

    /**
     * Domaine de l'adresse, ex: 'normandie-univ.fr'.
     */
    protected string $domain;

    /**
     * ED concernée éventuelle.
     */
    protected ?EcoleDoctorale $ecoleDoctorale = null;

    /**
     * Role concerné.
     */
    protected Role $role;

    /**
     * Etablissement concerné éventuel.
     */
    protected ?Etablissement $etablissement = null;

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getEcoleDoctoralePrefix(): string
    {
        return $this->ecoleDoctoralePrefix;
    }

    public function getCodesRolesAliases(): array
    {
        return $this->codesRolesAliases;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function setEcoleDoctorale(EcoleDoctorale $ecoleDoctorale = null): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
        return $this;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    public function setEtablissementAsStructure(?Structure $structure): self
    {
        if ($structure === null) {
            return $this->setEtablissement(null);
        }

        return $this->setEtablissement($structure->getEtablissement());
    }

    private function validateParams(): void
    {
        Assert::notNull($this->role, "Aucun rôle fourni");
        Assert::notNull($this->domain, "Aucun domaine fourni");
    }

    public function generateName(): string
    {
        $this->validateParams();

        $parts = [];
        $parts[] = $this->generateEcoleDoctoralePiece();
        $parts[] = $this->generateRolePiece();
        $parts[] = $this->generateEtablissementPiece();
        $parts = array_filter($parts);

        return implode($this->separator, $parts) . '@' . $this->domain;
    }

    private function generateEcoleDoctoralePiece(): ?string
    {
        if ($this->ecoleDoctorale === null) {
            return null;
        }
        if ($this->ecoleDoctorale->estTouteEcoleDoctoraleConfondue()) {
            return null;
        }

        return $this->ecoleDoctoralePrefix . trim($this->ecoleDoctorale->getStructure()->getCode()); // ex: 'ED590'
    }

    private function generateRolePiece(): string
    {
        $code = $this->role->getCode();

        return $this->codesRolesAliases[$code] ?? strtolower(str_replace('_-', '', $code));
    }

    private function generateEtablissementPiece(): ?string
    {
        if ($this->etablissement === null) {
            return null;
        }

        return strtolower($this->etablissement->getStructure()->getSourceCode()); // ex: 'UCN'
    }
}