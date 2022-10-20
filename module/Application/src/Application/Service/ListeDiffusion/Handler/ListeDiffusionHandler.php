<?php

namespace Application\Service\ListeDiffusion\Handler;

use These\Entity\Db\Acteur;
use Doctorant\Entity\Db\Doctorant;
use Structure\Entity\Db\Etablissement;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Role;
use These\Service\Acteur\ActeurServiceAwareTrait;
use Doctorant\Service\DoctorantServiceAwareTrait;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParserResult;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressParserResultWithED;
use Application\Service\Role\RoleServiceAwareTrait;
use Structure\Service\Structure\StructureServiceAwareTrait;
use BadMethodCallException;

class ListeDiffusionHandler extends ListeDiffusionAbstractHandler
{
    use StructureServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use ActeurServiceAwareTrait;
    use DoctorantServiceAwareTrait;
    use RoleServiceAwareTrait;

    /**
     * Rôle concerné.
     *
     * @var string|null
     */
    protected ?string $role = null;

    /**
     * Etablissement concerné éventuel.
     *
     * @var Etablissement
     */
    protected ?Etablissement $etablissement = null;

    /**
     * @inheritDoc
     */
    protected function parseAdresse()
    {
        parent::parseAdresse();

        $this->role = $this->parserResult->getRole();

        $etablissement = $this->parserResult->getEtablissement();
        if ($etablissement !== null) {
            $sourceCode = strtoupper($etablissement);
            $this->etablissement = $this->etablissementService->getRepository()->findOneBySourceCode($sourceCode);
        }
    }

    /**
     * @inheritDoc
     */
    public function canHandleListeDiffusion(): bool
    {
        $this->parseAdresse();

        return $this->role !== null;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->parseAdresse();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * Le contenu retourné contient une adresse électronique par ligne.
     *
     * @return string
     */
    public function createMemberIncludeFileContent(): string
    {
        $entities = $this->fetchMembers();
        $this->extractEmailsFromEntities($entities);

        return $this->createFileContent();
    }

    /**
     * @return Individu[]
     */
    protected function fetchMembers(): array
    {
        switch ($this->role) {
            case 'dirtheses':
                $acteurs = $this->fetchActeursDirecteursTheses();
                return array_map(function(Acteur $acteur) {
                    return $acteur->getIndividu();
                }, $acteurs);
            case 'doctorants':
                $acteurs = $this->fetchDoctorants();
                return array_map(function(Doctorant $doctorant) {
                    return $doctorant->getIndividu();
                }, $acteurs);
            default:
                return $this->fetchIndividusWithRole();
        }
    }

    /**
     * @return Acteur[]
     */
    protected function fetchActeursDirecteursTheses(): array
    {
        $critereEd = $this->computeCritereED();

        return $this->acteurService->getRepository()->findActeursByRole(
            Role::CODE_DIRECTEUR_THESE,
            $this->etablissement,
            $critereEd
        );
    }

    /**
     * @return Doctorant[]
     */
    protected function fetchDoctorants(): array
    {
        $critereEd = $this->computeCritereED();

        return $this->doctorantService->getRepository()->findByEcoleDoctAndEtab(
            $critereEd,
            $this->etablissement
        );
    }

    /**
     * @return array|null
     */
    protected function computeCritereED(): ?array
    {
        if ($this->parserResult instanceof ListeDiffusionAddressParserResultWithED) {
            $sigleSansEspace = $this->parserResult->getEcoleDoctorale();
            return ["REPLACE(%s.sigle,' ','')" => $sigleSansEspace];
        } elseif ($this->parserResult instanceof ListeDiffusionAddressParserResult) {
            return null;
        } else {
            throw new BadMethodCallException("Cas imprévu");
        }
    }

    /**
     * @return Individu[]
     */
    protected function fetchIndividusWithRole(): array
    {
        $code = strtoupper($this->role);
        $structure = $this->etablissement->getStructure();

        $role = $this->roleService->findOneByCodeAndStructure($code, $structure);

        return $this->individuService->getRepository()->findByRole($role);
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName(string $prefix): string
    {
        return sprintf('%sinclude_%s.inc',
            $prefix,
            $this->role
        );
    }
}