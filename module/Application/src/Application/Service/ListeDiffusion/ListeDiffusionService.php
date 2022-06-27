<?php

namespace Application\Service\ListeDiffusion;

use Structure\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\ListeDiffusion;
use Application\Entity\Db\Role;
use Structure\Entity\Db\Structure;
use Application\Service\BaseService;
use Individu\Service\IndividuServiceAwareTrait;
use Application\Service\ListeDiffusion\Address\ListeDiffusionAddressGenerator;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandlerInterface;
use Doctrine\ORM\ORMException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;
use Laminas\Mvc\Controller\Plugin\Url;

class ListeDiffusionService extends BaseService
{
    use IndividuServiceAwareTrait;

    /**
     * @var ListeDiffusionHandlerInterface[]
     */
    protected $availableHandlers = [];

    /**
     * @var ListeDiffusionHandlerInterface
     */
    protected $handler;

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
     * @var ListeDiffusion
     */
    protected $liste;

    /**
     * @param ListeDiffusionHandlerInterface[] $handlers
     * @return self
     */
    public function setAvailableHandlers(array $handlers)
    {
        $this->availableHandlers = $handlers;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(ListeDiffusion::class);
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
     * @param ListeDiffusion $liste
     * @return self
     */
    public function setListe(ListeDiffusion $liste)
    {
        $this->liste = $liste;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailDomain()
    {
        return $this->config['email_domain'];
    }

    /**
     * @return string
     */
    public function getUrlSympa()
    {
        return $this->config['sympa']['url'];
    }

    /**
     * @param ListeDiffusion $listeDiffusion
     * @return ListeDiffusionHandlerInterface
     */
    private function pickHandlerForListe(ListeDiffusion $listeDiffusion)
    {
        foreach ($this->availableHandlers as $handler) {
            $handler->setListeDiffusion($listeDiffusion);
            if ($handler->canHandleListeDiffusion()) {
                return $handler;
            }
        }
        throw new InvalidArgumentException("Oups, aucun handler compétent trouvé !");
    }

    /**
     * @return ListeDiffusion[]
     */
    public function fetchListesDiffusionActives()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Initialisation ind
     */
    public function init()
    {
        Assert::notNull($this->liste, "La liste cible doit être spécifiée avant d'appeler " . __METHOD__);
        $this->handler = $this->pickHandlerForListe($this->liste);
        $this->handler->init();
    }

    /**
     * @param EcoleDoctorale|null $ed
     * @param Role|null $role
     * @param Structure|null $etablissement
     * @return ListeDiffusionAddressGenerator
     */
    public function createNameGenerator(EcoleDoctorale $ed = null, Role $role = null, Structure $etablissement = null)
    {
        $ng = new ListeDiffusionAddressGenerator();
        $ng->setEcoleDoctorale($ed);
        $ng->setRole($role);
        $ng->setEtablissementAsStructure($etablissement);

        return $ng;
    }

    private function checkHandler()
    {
        Assert::notNull($this->handler, "Aucun handler courant, avez-vous appelé init() auparavant ?");
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les ABONNÉS d'une liste de diffusion.
     *
     * @return string
     */
    public function createMemberIncludeFileContent()
    {
        $this->checkHandler();

        return $this->handler->createMemberIncludeFileContent();
    }

    /**
     * Génération du contenu du fichier attendu par Sympa pour obtenir les PROPRIÉTAIRES d'une liste de diffusion.
     *
     * @return string
     */
    public function createOwnerIncludeFileContent()
    {
        $this->checkHandler();

        return $this->handler->createOwnerIncludeFileContent();
    }

    /**
     * @return string[] [mail => nom individu]
     */
    public function getIndividusAvecAdresse()
    {
        $this->checkHandler();

        return $this->handler->getIndividusAvecAdresse();
    }

    /**
     * @return string[] [id individu => nom individu]
     */
    public function getIndividusSansAdresse()
    {
        $this->checkHandler();

        return $this->handler->getIndividusSansAdresse();
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateResultFileName(string $prefix)
    {
        $this->checkHandler();

        return $this->handler->generateResultFileName($prefix);
    }

    /**
     * @param string $adresse
     * @return ListeDiffusion|null
     */
    public function findListeDiffusionByAdresse(string $adresse)
    {
        /** @var ListeDiffusion $liste */
        $liste = $this->getRepository()->findOneBy(['adresse' => $adresse]);

        return $liste;
    }

    /**
     * @param string[] $adresses
     * @return int
     */
    public function deleteListesDiffusions(array $adresses)
    {
        /** @var ListeDiffusion $liste */
        $qb = $this->getRepository()->createQueryBuilder('ld');
        $qb
            ->delete(null, 'ld')
            ->where($qb->expr()->in('ld.adresse', $adresses));

        return $qb->getQuery()->execute();
    }

    /**
     * @param array $data
     * @return ListeDiffusion
     */
    public function createListeDiffusion(array $data)
    {
        $adresse = $data['adresse'];
        $enabled = $data['enabled']  ?? true;

        $liste = new ListeDiffusion();
        $liste
            ->setAdresse($adresse)
            ->setEnabled($enabled);

        return $liste;
    }

    /**
     * @param ListeDiffusion $listeDiffusion
     * @param array $data
     */
    public function updateListeDiffusion(ListeDiffusion $listeDiffusion, array $data)
    {
        $adresse = $data['adresse'];
        $enabled = $data['enabled']  ?? true;

        $listeDiffusion
            ->setAdresse($adresse)
            ->setEnabled($enabled);
    }

    /**
     * @param ListeDiffusion[] $listes
     * @throws ORMException
     */
    public function saveListesDiffusions(array $listes)
    {
        foreach ($listes as $liste) {
            if ($liste->getId() === null) {
                $this->entityManager->persist($liste);
            }
        }
        $this->entityManager->flush($listes);
    }

    /**
     * @param Url $urlPlugin
     * @return array
     */
    public function createDataForCsvExport(Url $urlPlugin)
    {
        $data = [];
        $data[] = [
            "Liste",
            "URL Membres",
            "URL Propriétaires",
        ];
        $listesDiffusionActives = $this->fetchListesDiffusionActives();
        foreach ($listesDiffusionActives as $listeDiffusion) {
            $data[] = [
                $listeDiffusion->getAdresse(),
                $urlPlugin->fromRoute('liste-diffusion/liste/generate-member-include',
                    ['adresse' => $listeDiffusion->getAdresse()], ['force_canonical' => true]),
                $urlPlugin->fromRoute('liste-diffusion/liste/generate-owner-include',
                    ['adresse' => $listeDiffusion->getAdresse()], ['force_canonical' => true]) ,
            ];
        }

        return $data;
    }
}