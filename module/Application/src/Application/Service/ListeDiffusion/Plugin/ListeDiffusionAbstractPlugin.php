<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Interfaces\IndividuAwareInterface;
use Application\Entity\Db\Role;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\ListeDiffusion\ListeDiffusionParser;

abstract class ListeDiffusionAbstractPlugin implements ListeDiffusionPluginInterface
{
    use IndividuServiceAwareTrait;

    /**
     * @var string[]
     */
    protected $config = [];

    /**
     * @var ListeDiffusionParser
     */
    protected $parser;

    /**
     * Nom de la liste de diffusion.
     *
     * Le nom de liste peut avoir 3 formes :
     * 1/ ed591.doctorants.insa@normandie-univ.fr
     * 2/ ed591.doctorants@normandie-univ.fr
     * 3/ ed591.dirtheses@normandie-univ.fr
     *
     * Dans lesquelles :
     * - '591' est le numéro national de l'école doctorale ;
     * - 'doctorants' est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     *
     * @var string
     */
    protected $liste;

    /**
     * @var Individu[]
     */
    protected $individusAvecAdresse = [];

    /**
     * @var Individu[]
     */
    protected $individusSansAdresse = [];

    /**
     * ListeDiffusionAbstractPlugin constructor.
     */
    public function __construct()
    {
        $this->parser = new ListeDiffusionParser();
    }

    /**
     * @param string[] $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

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
     * @return string[] [mail => nom individu]
     */
    public function getIndividusAvecAdresse()
    {
        return $this->individusAvecAdresse;
    }

    /**
     * @return string[] [id individu => nom individu]
     */
    public function getIndividusSansAdresse()
    {
        return $this->individusSansAdresse;
    }

    /**
     * @return Individu[]|string[]
     */
    public function fetchProprietaires()
    {
        //return $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
        return (array) $this->config['proprietaires'] ?? [];
    }

    /**
     * @return string
     */
    protected function createFileContent()
    {
        $adresses = array_unique($this->individusAvecAdresse);

        $lines = [];
        foreach ($adresses as $adresse => $nom) {
            $lines[] = $adresse . ' ' . $nom;
        }
        sort($lines);

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param IndividuAwareInterface[]|Individu[]|string[] $entities
     */
    protected function extractEmailsFromEntities(array $entities)
    {
        $adresses = [];
        $individusSansAdresse = [];
        foreach ($entities as $key => $entity) {
            if (is_string($entity)) {
                $email = trim($key);
                $nom = $entity;
            } else {
                $individu = $entity instanceof IndividuAwareInterface ? $entity->getIndividu() : $entity;
                $email = trim($this->extractEmailFromIndividu($individu));
                $nom = $individu->getNomComplet();
            }
            if ($email) {
                $adresses[$email] = $nom;
            } else {
                $individusSansAdresse[$nom] = $nom;
            }
        }

        $this->individusAvecAdresse = array_unique(array_filter($adresses));
        $this->individusSansAdresse = array_unique(array_filter($individusSansAdresse));
    }

    /**
     * @param Individu $individu
     * @return string|null
     */
    private function extractEmailFromIndividu(Individu $individu)
    {
        return $individu->getEmail() ?: $individu->getMailContact() ?: $individu->getEmailUtilisateur();
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName($prefix)
    {
        return sprintf('%sinclude.inc',
            $prefix
        );
    }
}