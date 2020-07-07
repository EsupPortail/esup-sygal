<?php

namespace Application\Service\ListeDiffusion;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Interfaces\IndividuAwareInterface;
use Application\Entity\Db\Role;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\BaseService;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use Webmozart\Assert\Assert;

class ListeDiffusionService extends BaseService
{
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;

    const CIBLE_DOCTORANT = 'doctorants';
    const CIBLE_DIR_THESE = 'dirtheses';
    const CIBLES = [
        self::CIBLE_DOCTORANT,
        self::CIBLE_DIR_THESE,
    ];

    /**
     * @var string[]
     */
    protected $config = [];

    /**
     * Numéro national de l'ED concernée.
     *
     * @var string
     */
    protected $ecoleDoctorale;

    /**
     * Etablissement concerné *éventuel*.
     *
     * @var Etablissement
     */
    protected $etablissement = null;

    /**
     * Valeur parmi {@see ListeDiffusionService::CIBLES}.
     *
     * @var string
     */
    protected $cible;

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
     * @inheritDoc
     */
    public function getRepository()
    {
        throw new LogicException("Non pertinent !");
    }

    /**
     * @param string[] $config
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
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
     * @param EcoleDoctorale|string $ecoleDoctorale
     * @return self
     */
    public function setEcoleDoctorale($ecoleDoctorale)
    {
        $this->ecoleDoctorale = $ecoleDoctorale;

        return $this;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement = null)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @param string $cible
     * @return self
     */
    public function setCible($cible)
    {
        Assert::inArray($cible, self::CIBLES, "Cible %s spécifiée inconnue.");

        $this->cible = $cible;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getIndividusAvecAdresse()
    {
        return $this->individusAvecAdresse;
    }

    /**
     * @return string[]
     */
    public function getIndividusSansAdresse()
    {
        return $this->individusSansAdresse;
    }

    /**
     * @return string[]
     */
    public function fetchListesDiffusion()
    {
        return (array) $this->config['listes'] ?? [];
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createMemberIncludeFileContent()
    {
        switch ($this->cible) {
            case self::CIBLE_DIR_THESE:
                $entities = $this->fetchActeursDirecteursTheses();
                break;
            case self::CIBLE_DOCTORANT:
                $entities = $this->fetchDoctorants();
                break;
            default:
                $entities = [];
        }
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent()
    {
        $entities = $this->fetchOwners();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
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
     * @return Acteur[]
     */
    private function fetchActeursDirecteursTheses()
    {
        return $this->acteurService->getRepository()->findActeursWithRoleAndEcoleDoctAndEtab(
            Role::CODE_DIRECTEUR_THESE,
            $this->ecoleDoctorale,
            $this->etablissement
        );
    }

    /**
     * @return Doctorant[]
     */
    private function fetchDoctorants()
    {
        return $this->doctorantService->getRepository()->findByEtabAndEcoleDoct(
            $this->ecoleDoctorale,
            $this->etablissement
        );
    }

    /**
     * @return Individu[]
     */
    public function fetchOwners()
    {
        return $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
    }

    /**
     * @param IndividuAwareInterface[]|Individu[] $entities
     */
    private function extractEmailsFromEntities(array $entities)
    {
        $adresses = [];
        $individusSansAdresse = [];
        foreach ($entities as $entity) {
            $individu = $entity instanceof IndividuAwareInterface ? $entity->getIndividu() : $entity;
            if ($email = trim($individu->getEmail() ?: $individu->getMailContact())) {
                $adresses[$email] = $individu->getNomComplet();
            } else {
                $individusSansAdresse[$individu->getId()] = $individu->getNomComplet();
            }
        }

        $this->individusAvecAdresse = array_unique(array_filter($adresses));
        $this->individusSansAdresse = array_unique(array_filter($individusSansAdresse));
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName($prefix)
    {
        return sprintf('%sinclude_%s_%s_%s.inc',
            $prefix,
            $this->ecoleDoctorale,
            $this->cible,
            $this->etablissement ? $this->etablissement->getCode() : 'etabs'
        );
    }
}