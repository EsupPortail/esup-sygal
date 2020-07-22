<?php

namespace Application\Service\ListeDiffusion;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

class ListeDiffusionParser
{
    const REGEXP_ECOLE_DOCTORALE = '/^(ed)\d+$/';
    const REGEXP_UNITE_RECHERCHE = '/^(ur)[a-zA-Z0-9-]+$/';
    const PSEUDO_STRUCTURE_SYGAL = 'sygal';

    /**
     * Numéro national de l'ED concernée.
     *
     * @var string
     */
    protected $ecoleDoctorale;

    /**
     * Code de la structure concernée.
     *
     * @var string
     */
    protected $structure;

    /**
     * Role concerné éventuel.
     *
     * @var string
     */
    protected $role;

    /**
     * Etablissement concerné éventuel.
     *
     * @var string
     */
    protected $etablissement;

    /**
     * Cible, ex: 'doctorants'.
     *
     * @var string
     */
    protected $cible;

    /**
     * Adresse complète de la liste de diffusion, ex :
     *   - ed591.doctorants.insa@normandie-univ.fr
     *   - ed591.doctorants@normandie-univ.fr
     *   - ed591.dirtheses@normandie-univ.fr
     *
     * Où :
     * - '591' est le numéro national de l'école doctorale ;
     * - 'doctorants' (ou 'dirtheses') est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     *
     * @var string
     */
    protected $liste;

    /**
     * @var string[]
     */
    protected $listeElements;

    /**
     * @param string $liste
     * @return self
     */
    public function setListe($liste)
    {
        $this->liste = $liste;

        return $this;
    }

    /**
     * @return self
     */
    public function parse()
    {
        $this->listeElements = explode('@', $this->liste)[0]; // ex: 'ed591.doctorants.insa'
        $this->listeElements = explode('.', $this->listeElements); // ex: ['ed591', 'doctorants', 'insa']

        $listeElements = $this->listeElements;

        $this->structure = array_shift($listeElements); // ex: 'ed591', 'sygal' // todo: 'ur32115' un jour ?
        Assert::notNull($this->structure, "Aucune structure spécifiée.");

        $this->cible = array_shift($listeElements); // ex: 'doctorants', 'role'

        if ($this->isTypeListeStructure()) {
            if ($this->isStructureEcoleDoctorale()) {
                $this->parseForEcoleDoctorale($listeElements);
            } elseif ($this->isStructureUniteRecherche()) {
                throw new InvalidArgumentException("Non implémenté.");
            }
        }
        elseif ($this->isTypeListeRole()) {
            $this->parseForRole($listeElements);
        }
        else  {
            throw new InvalidArgumentException("Cas non prévu.");
        }

        return $this;
    }

    private function isStructureEcoleDoctorale()
    {
        return preg_match(self::REGEXP_ECOLE_DOCTORALE, $this->structure);
    }

    private function isStructureUniteRecherche()
    {
        return preg_match(self::REGEXP_UNITE_RECHERCHE, $this->structure);
    }

    public function isTypeListeStructure()
    {
        return
            $this->isStructureEcoleDoctorale() ||
            $this->isStructureUniteRecherche();
    }

    /**
     * @return bool
     */
    public function isTypeListeRole()
    {
        return $this->structure === self::PSEUDO_STRUCTURE_SYGAL && $this->cible === 'role';
    }

    private function parseForEcoleDoctorale(array $listeElements)
    {
        Assert::regex($this->structure, $p = self::REGEXP_ECOLE_DOCTORALE, "L'ED spécifiée n'est au format attendu");
        Assert::notNull($this->cible, "Aucune cible spécifiée.");
        $this->etablissement = array_shift($listeElements); // ex: 'insa' ou NULL
        $this->ecoleDoctorale = substr($this->structure, 2);
    }

    private function parseForRole(array $listeElements)
    {
        $role = array_shift($listeElements); // ex: 'admin_tech'
        Assert::notNull($role, "Aucun rôle spécifié.");

        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getEcoleDoctorale()
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * @return string
     */
    public function getCible()
    {
        return $this->cible;
    }
}