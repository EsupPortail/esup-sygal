<?php

namespace InscriptionAdministrative\Entity\Db;

use Doctorant\Entity\Db\Doctorant;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareInterface;
use UnicaenUtilisateur\Entity\Db\HistoriqueAwareTrait;
use UnicaenDbImport\Entity\Db\Traits\SourceAwareTrait;

/********************************************************************************************************************
 * Inscriptions administratives d'un doctorant
 ********************************************************************************************************************
 *
 * Rappel du problème avec les inscriptions administratives reçues de Pégase :
 * {@see \InscriptionAdministrative\Entity\Db\InscriptionAdministrative}
 *
 * Cette classe d'entité se propose de fournir les inscriptions pertinentes et sans doublon d'un doctorant.
 *
 */
class InscriptionAdministrativeDoctorant implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use SourceAwareTrait;

    private int $id;
    private string $sourceCode;

    private InscriptionAdministrative $inscriptionAdministrative;
    private Doctorant $doctorant;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    public function getInscriptionAdministrative(): InscriptionAdministrative
    {
        return $this->inscriptionAdministrative;
    }

    public function setInscriptionAdministrative(InscriptionAdministrative $inscriptionAdministrative): self
    {
        $this->inscriptionAdministrative = $inscriptionAdministrative;
        return $this;
    }

    public function getDoctorant(): Doctorant
    {
        return $this->doctorant;
    }

    public function setDoctorant(Doctorant $doctorant): self
    {
        $this->doctorant = $doctorant;
        return $this;
    }
}