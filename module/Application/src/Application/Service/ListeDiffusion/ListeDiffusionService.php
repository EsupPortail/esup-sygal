<?php

namespace Application\Service\ListeDiffusion;

use Application\Service\BaseService;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionPluginInterface;
use UnicaenApp\Exception\LogicException;

class ListeDiffusionService extends BaseService
{
    use IndividuServiceAwareTrait;

    /**
     * @var ListeDiffusionPluginInterface[]
     */
    protected $listeDiffusionServicePlugins = [];

    /**
     * @var ListeDiffusionPluginInterface
     */
    protected $listeDiffusionServicePluginForListe;

    /**
     * @var string[]
     */
    protected $config = [];

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
     * @param ListeDiffusionPluginInterface[] $listeDiffusionServicePlugins
     * @return self
     */
    public function setListeDiffusionServicePlugins(array $listeDiffusionServicePlugins)
    {
        $this->listeDiffusionServicePlugins = $listeDiffusionServicePlugins;

        return $this;
    }

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
     * @return ListeDiffusionPluginInterface|null
     */
    protected function getListeDiffusionServicePluginForListe()
    {
        if ($this->listeDiffusionServicePluginForListe === null) {
            foreach ($this->listeDiffusionServicePlugins as $plugin) {
                $plugin->setListe($this->liste);
                if ($plugin->canHandleListe()) {
                    $this->listeDiffusionServicePluginForListe = $plugin;
                    break;
                }
            }
            if ($this->listeDiffusionServicePluginForListe === null) {
                throw new \InvalidArgumentException("Oups, aucun plugin compétent trouvé !");
            }
        }

        return $this->listeDiffusionServicePluginForListe;
    }

    /**
     * @return string[]
     */
    public function fetchListesDiffusion()
    {
        return (array) $this->config['listes'] ?? [];
    }

    /**
     *
     */
    public function init()
    {
        $listeDiffusionServicePluginForListe = $this->getListeDiffusionServicePluginForListe();
        $listeDiffusionServicePluginForListe->setListe($this->liste);
        $listeDiffusionServicePluginForListe->init();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * @return string
     */
    public function createMemberIncludeFileContent()
    {
        return $this->getListeDiffusionServicePluginForListe()->createMemberIncludeFileContent();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent()
    {
        return $this->getListeDiffusionServicePluginForListe()->createOwnerIncludeFileContent();
    }

    /**
     * @return string[] [mail => nom individu]
     */
    public function getIndividusAvecAdresse()
    {
        return $this->getListeDiffusionServicePluginForListe()->getIndividusAvecAdresse();
    }

    /**
     * @return string[] [id individu => nom individu]
     */
    public function getIndividusSansAdresse()
    {
        return $this->getListeDiffusionServicePluginForListe()->getIndividusSansAdresse();
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