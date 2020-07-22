<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\Doctorant\DoctorantServiceAwareTrait;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Webmozart\Assert\Assert;

class ListeDiffusionStructurePlugin extends ListeDiffusionAbstractPlugin
{
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use EtablissementServiceAwareTrait;

    const CIBLE_DOCTORANT = 'doctorants';
    const CIBLE_DIR_THESE = 'dirtheses';
    const CIBLES = [
        self::CIBLE_DOCTORANT,
        self::CIBLE_DIR_THESE,
    ];

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
     * @inheritDoc
     */
    public function canHandleListe()
    {
        $this->parser
            ->setListe($this->liste)
            ->parse();

        return $this->parser->isTypeListeStructure();
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->parser
            ->setListe($this->liste)
            ->parse();

        $this->ecoleDoctorale = $this->parser->getEcoleDoctorale();
        // NB : on ne fetche pas l'ED dans la base de données car plusieurs enregistrements peuvent exister dans la
        //      table des ED pour un même code national. Ce code est exploiter plus tard au moment de la recherche
        //      des abonnés à une liste de diffusion.

        $this->cible = $this->parser->getCible();
        Assert::inArray($this->cible, self::CIBLES);

        $etablissement = $this->parser->getEtablissement();
        if ($etablissement !== null) {
            $this->etablissement = $this->etablissementService->getRepository()->findOneBySourceCode(strtoupper($etablissement));
        }
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
        $entities = $this->fetchProprietaires();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
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