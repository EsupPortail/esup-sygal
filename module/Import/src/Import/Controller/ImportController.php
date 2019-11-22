<?php

namespace Import\Controller;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Exception\StructureNotFoundException;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Assert\Assertion;
use Doctrine\ORM\EntityManager;
use Import\Exception\CallException as ImportCallException;
use Import\Service\Traits\ImportServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\Filter\Priority;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ImportController extends AbstractActionController
{
    use EntityManagerAwareTrait;
    use ImportServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /**
     * @var array $config [ 'CODE_ETABLISSEMENT' => [...] ]
     */
    protected $config;

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return ViewModel
     * @throws \Assert\AssertionFailedException
     */
    public function indexAction()
    {
        Assertion::keyIsset($this->config, 'etablissements');

        $codesEtablissements = array_keys($this->config['etablissements']);

        return new ViewModel([
            'codesEtablissements' => $codesEtablissements,
        ]);
    }

    public function apiInfoAction()
    {
        $codeStructure = $this->params()->fromRoute("etablissement"); // ex: 'UCN'

        try {
            $etablissement = $this->fetchEtablissementByCodeStructure($codeStructure);
            $version = $this->importService->getApiVersion($etablissement);
            $error = null;
        } catch (StructureNotFoundException $e) {
            $version = "Inconnue";
            $error = $e->getMessage();
        } catch (ImportCallException $e) {
            $version = "Inconnue";
            $error = $e->getMessage() . " : " . $e->getPrevious()->getMessage();
        }

        return [
            'version' => $version,
            'error' => $error,
        ];
    }

    /**
     * @param string $sourceCode SOURCE_CODE de l'établissement, ex: 'UCN'
     * @return Etablissement
     * @throws StructureNotFoundException
     */
    private function fetchEtablissementByCodeStructure($sourceCode)
    {
        $etablissement = $this->etablissementService->getRepository()->findOneBySourceCode($sourceCode);
        if ($etablissement === null) {
            throw new StructureNotFoundException("Aucun établissement trouvé avec le code structure " . $sourceCode);
        }

        return $etablissement;
    }

    public function launcherAction()
    {
        return false; // todo: la page doit être rénovée...
    }

    /**
     * @return ViewModel
     * @throws \Doctrine\DBAL\DBALException
     */
    public function infoLastUpdateAction()
    {
        $etablissement = $this->params()->fromRoute("etablissement");
        $table = $this->params()->fromRoute("table");

        $connection = $this->entityManager->getConnection();
        $result = $connection->executeQuery("SELECT REQ_END_DATE, REQ_RESPONSE FROM API_LOG WHERE REQ_ETABLISSEMENT='" . $etablissement . "' AND REQ_TABLE='" . $table . "' ORDER BY REQ_END_DATE DESC");
        $data = $result->fetch();

        $last_time = $data["REQ_END_DATE"];
        $message = $data["REQ_RESPONSE"];

        return new ViewModel([
            'query'     => $etablissement . ' | ' . $table,
            "last_time" => $last_time,
            "message"   => $message,
        ]);
    }

    public function helpAction()
    {
        return new ViewModel();
    }

    /**
     * Interroge le WS pour récupérer les données d'un seul établissement puis lance la synchronisation des données obtenues
     * avec les tables destinations.
     *
     * @return ViewModel
     * @throws StructureNotFoundException
     */
    public function importAction()
    {
        $service = $this->params('service');
        $codeStructure = $this->params('etablissement'); // ex: 'UCN'
        $sourceCode = $this->params('source_code');
        $verbose = (bool) $this->params('verbose', 0);

        $queryParams = $this->params()->fromQuery();

        $etablissement = $this->fetchEtablissementByCodeStructure($codeStructure);

        $stream = fopen('php://memory','r+');
        $this->setLoggerStream($stream, $verbose);

        $this->importService->import($service, $etablissement, $sourceCode, $queryParams);

        rewind($stream);
        $logs = stream_get_contents($stream);
        fclose($stream);

        return new ViewModel([
            'service'       => $service,
            'etablissement' => $etablissement,
            'source_code'   => $sourceCode,
            'logs'          => $logs,
        ]);
    }

    /**
     * Interroge le WS pour récupérer toutes les données d'un établissement puis lance la synchronisation
     * des données obtenues avec les tables destinations.
     *
     * @return ViewModel
     * @throws StructureNotFoundException
     */
    public function importAllAction()
    {
        $codeStructure = $this->params('etablissement'); // ex: 'UCN'
        $verbose = (bool) $this->params('verbose', 0);

        $etablissement = $this->fetchEtablissementByCodeStructure($codeStructure);

        $stream = fopen('php://memory','r+');
        $this->setLoggerStream($stream, $verbose);

        $this->importService->importAll($etablissement);

        rewind($stream);
        $logs = stream_get_contents($stream);
        fclose($stream);

        return new ViewModel([
            'service'       => 'Tous',
            'etablissement' => $etablissement,
            'source_code'   => '-',
            'logs'          => $logs,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function updateTheseAction()
    {
        $codeEtablissement = $this->params('etablissement');
        $sourceCodeThese = $this->params('source_code');
        $verbose = (bool) $this->params('verbose', 0);

        if (! $sourceCodeThese) {
            throw new LogicException("Le source code de la thèse est requis");
        }

        $sourceCodeThese = $this->sourceCodeStringHelper->addPrefixTo($sourceCodeThese, $codeEtablissement);

        /** @var These $these */
        $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $sourceCodeThese]);
        if ($these === null) {
            throw new RuntimeException("Aucune thèse trouvée avec ce source code: " . $sourceCodeThese);
        }

        $stream = fopen('php://memory','r+');
        $this->setLoggerStream($stream, $verbose);

        $this->importService->updateThese($these);

        rewind($stream);
        $logs = stream_get_contents($stream);
        fclose($stream);

        return new ViewModel([
            'service'       => "these + dépendances",
            'etablissement' => $codeEtablissement,
            'source_code'   => $sourceCodeThese,
            'logs'          => $logs,
        ]);
    }

    /**
     * @throws StructureNotFoundException
     */
    public function importConsoleAction()
    {
        $service = $this->params('service');
        $codeStructure = $this->params('etablissement'); // ex: 'UCN'
        $sourceCode = $this->params('source-code');
        $synchronize = (bool) $this->params('synchronize', 1);
        $emName = $this->params('em', 'orm_default');
        $verbose = (bool) $this->params('verbose', 0);

        $this->setLoggerStream('php://output', $verbose);

        $etablissement = $this->fetchEtablissementByCodeStructure($codeStructure);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get("doctrine.entitymanager.$emName");

        $_deb = microtime(true);
        $this->importService->setEntityManager($entityManager);
        $this->importService->import($service, $etablissement, $sourceCode, [], $synchronize);
        $_fin = microtime(true);

        echo sprintf(
            "Importation des données du service '%s' de l'établissement '%s' effectuée en %.2f secondes.",
            $service,
            $etablissement,
            $_fin - $_deb
        ) . PHP_EOL;
    }

    /**
     * @throws StructureNotFoundException
     */
    public function importAllConsoleAction()
    {
        $codeStructure = $this->params('etablissement'); // ex: 'UCN'
        $breakOnServiceNotFound = (bool) $this->params('breakOnServiceNotFound', 1);
        $synchronize = (bool) $this->params('synchronize', 1);
        $emName = $this->params('em', 'orm_default');
        $verbose = (bool) $this->params('verbose', 0);

        $etablissement = $this->fetchEtablissementByCodeStructure($codeStructure);

        $this->setLoggerStream('php://output', $verbose);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get("doctrine.entitymanager.$emName");

        $_deb = microtime(true);
        $this->importService->setEntityManager($entityManager);
        $this->importService->importAll($etablissement, $synchronize, $breakOnServiceNotFound);
        $_fin = microtime(true);

        echo sprintf(
            "Importation de toutes les données de l'établissement '%s' effectuée en %.2f secondes.",
            $etablissement,
            $_fin - $_deb
        ) . PHP_EOL;
    }

    public function updateTheseConsoleAction()
    {
        $id = $this->params('id');
        $emName = $this->params('em', 'orm_default');
        $verbose = (bool) $this->params('verbose', 0);

        /** @var These $these */
        $these = $this->theseService->getRepository()->find($id);

        $this->setLoggerStream('php://output', $verbose);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get("doctrine.entitymanager.$emName");

        $_deb = microtime(true);
        $this->importService->setEntityManager($entityManager);
        $this->importService->updateThese($these);
        $_fin = microtime(true);

        echo sprintf(
                "Mise à jour de la thèse %d de l'établissement '%s' effectuée en %f s.",
                $these->getId(),
                $these->getEtablissement()->getSourceCode(),
                $_fin - $_deb
            ) . PHP_EOL;
    }

    /**
     * @param string|resource $stream
     * @param bool            $verbose
     */
    private function setLoggerStream($stream, $verbose = false)
    {
        $filter = new Priority($verbose ? Logger::DEBUG : Logger::INFO);

        $writer = new Stream($stream);
        $writer->addFilter($filter);

        /** @var Logger $logger */
        $logger = $this->importService->getLogger();
        $logger->addWriter($writer);
    }
}
