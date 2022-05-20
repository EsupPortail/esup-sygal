<?php

namespace Application\Service\ListeDiffusion\Handler;

use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Application\Entity\Db\ListeDiffusion;
use Individu\Service\IndividuServiceAwareTrait;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParser;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParserResult;
use Webmozart\Assert\Assert;

abstract class ListeDiffusionAbstractHandler implements ListeDiffusionHandlerInterface
{
    use IndividuServiceAwareTrait;

    /**
     * @var string[]
     */
    protected $config = [];

    /**
     * @var ListeDiffusionAddressParser
     */
    protected $parser;

    /**
     * @var ListeDiffusionAddressParserResult
     */
    protected $parserResult;

    /**
     * Liste de diffusion.
     *
     * Le nom de liste peut avoir 3 formes :
     * 1/ ED591NBISE.doctorants.insa@normandie-univ.fr
     * 2/ ED591NBISE.doctorants@normandie-univ.fr
     * 3/ ED591NBISE.dirtheses@normandie-univ.fr
     *
     * Dans lesquelles :
     * - 'ED591NBISE' est le nom de l'école doctorale sans espace ;
     * - 'doctorants' est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     *
     * @var ListeDiffusion
     */
    protected $listeDiffusion;

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
        $this->parser = new ListeDiffusionAddressParser();
    }

    /**
     * @param string[] $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ListeDiffusion $listeDiffusion
     * @return self
     */
    public function setListeDiffusion(ListeDiffusion $listeDiffusion)
    {
        $this->listeDiffusion = $listeDiffusion;
        $this->parserResult = null;

        return $this;
    }

    /**
     * Parse l'adresse de la liste de diffusion courante.
     */
    protected function parseAdresse()
    {
        if ($this->parserResult === null) {
            Assert::notNull($this->listeDiffusion, "Aucune liste à parser");
            $this->parserResult = $this->parser
                ->setAddress($this->listeDiffusion->getAdresse())
                ->parse();
        }
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
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    abstract public function createMemberIncludeFileContent();

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent()
    {
        $entities = $this->fetchProprietaires();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
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
                $emails = ($email = trim($key)) ? [$email] : [];
                $nom = $entity;
            } else {
                $individu = $entity instanceof IndividuAwareInterface ? $entity->getIndividu() : $entity;
                $emails = $this->extractEmailFromIndividu($individu);
                $nom = $individu->getNomComplet();
            }
            if (!empty($emails)) {
                foreach ($emails as $email) {
                    $adresses[$email] = $nom;
                }
            } else {
                $individusSansAdresse[$nom] = $nom;
            }
        }

        $this->individusAvecAdresse = array_filter($adresses);
        $this->individusSansAdresse = array_filter($individusSansAdresse);
    }

    /**
     * @param Individu $individu
     * @return string[]
     */
    private function extractEmailFromIndividu(Individu $individu)
    {
        return array_map('trim', array_unique(array_filter([
            $individu->getEmailContact(),
            $individu->getEmail(),
            $individu->getEmailUtilisateur(),
        ])));
    }

    /**
     * @param string $prefix
     * @return string
     */
    abstract public function generateResultFileName(string $prefix);
}