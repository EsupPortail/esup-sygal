<?php

namespace Application\Service\ListeDiffusion\Handler;

use Application\Entity\Db\ListeDiffusion;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParser;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParserResult;
use Individu\Entity\Db\Individu;
use Individu\Entity\Db\IndividuAwareInterface;
use Individu\Service\IndividuServiceAwareTrait;
use Webmozart\Assert\Assert;

abstract class ListeDiffusionAbstractHandler implements ListeDiffusionHandlerInterface
{
    use IndividuServiceAwareTrait;

    /**
     * @var string[]
     */
    protected array $config = [];

    protected ListeDiffusionAddressParser $parser;
    protected ?ListeDiffusionAddressParserResult $parserResult = null;

    /**
     * Liste de diffusion.
     *
     * Le nom de liste peut avoir 3 formes :
     * 1/ ED591.doctorants.insa@normandie-univ.fr
     * 2/ ED591.doctorants@normandie-univ.fr
     * 3/ ED591.dirtheses@normandie-univ.fr
     *
     * Dans lesquelles :
     * - 'ED591' est le numéro/code de l'école doctorale avec un préfixe ;
     * - 'doctorants' est la "cible" ;
     * - 'insa' est le source_code unique de l'établissement en minuscules.
     */
    protected ?ListeDiffusion $listeDiffusion = null;

    /**
     * @var Individu[]
     */
    protected array $individusAvecAdresse = [];

    /**
     * @var Individu[]
     */
    protected array $individusSansAdresse = [];

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
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function setListeDiffusion(ListeDiffusion $listeDiffusion): self
    {
        $this->listeDiffusion = $listeDiffusion;
        $this->parserResult = null;

        return $this;
    }

    /**
     * Parse l'adresse de la liste de diffusion courante.
     */
    protected function parseAdresse(): void
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
    public function getIndividusAvecAdresse(): array
    {
        return $this->individusAvecAdresse;
    }

    /**
     * @return string[] [id individu => nom individu]
     */
    public function getIndividusSansAdresse(): array
    {
        return $this->individusSansAdresse;
    }

    /**
     * @return Individu[]|string[]
     */
    public function fetchProprietaires(): array
    {
        //return $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
        return (array) $this->config['proprietaires'] ?? [];
    }

    protected function createFileContent(): string
    {
        $lines = [];
        foreach ($this->individusAvecAdresse as $adresse => $nom) {
            $lines[] = $adresse . ' ' . $nom;
        }
        sort($lines);

        return implode(PHP_EOL, $lines);
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     */
    abstract public function createMemberIncludeFileContent(): string;

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     */
    public function createOwnerIncludeFileContent(): string
    {
        $entities = $this->fetchProprietaires();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
    }

    /**
     * @param IndividuAwareInterface[]|Individu[]|string[] $entities
     */
    protected function extractEmailsFromEntities(array $entities): void
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
    private function extractEmailFromIndividu(Individu $individu): array
    {
        return array_map('trim', array_unique(array_filter([
            $individu->getEmailContactAutorisePourListeDiff() ? $individu->getEmailContact() : null, // respect du consentement
            $individu->getEmailPro(),
            $individu->getEmailUtilisateur(),
        ])));
    }

    abstract public function generateResultFileName(string $prefix): string;
}