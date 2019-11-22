<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\SourceCodeStringHelperAwareTrait;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\ORM\EntityManager;
use Import\Service\Traits\CallServiceAwareTrait;
use Import\Service\Traits\DbServiceAwareTrait;
use stdClass;
use UnicaenApp\Exception\RuntimeException;
use Zend\Log\LoggerAwareTrait;
use Zend\Log\LoggerInterface;

/**
 * Service réalisant :
 * - la récupération des données provenant du web service d'import,
 * - l'enregsitrement des données retournées dans les tables temporaires de la bdd,
 * un établissement à la fois.
 */
class FetcherService
{
    use CallServiceAwareTrait;
    use DbServiceAwareTrait;
    use LoggerAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /**
     * @var array $config [ 'CODE_ETABLISSEMENT' => [...] ]
     */
    protected $config;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * Force le gestionnaire d'entités que doit utiliser le DbService.
     *
     * NB: le nom de ce setter tente d'informer que la classe FetcherService n'a pas besoin d'entity manager.
     *
     * @param EntityManager $entityManager
     * @return self
     */
    public function setEntityManagerForDbService(EntityManager $entityManager)
    {
        $this->dbService->setEntityManager($entityManager);

        return $this;
    }

    /**
     * Set logger object
     *
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->callService->setLogger($this->logger);
        $this->dbService->setLogger($this->logger);

        return $this;
    }

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function version()
    {
        $config = $this->getConfigForEtablissement();
        $config['timeout'] = 10;

        $this->callService->setConfig($config);

        return $this->callService->getVersion();
    }

    /**
     * Demande au WS d'import tous les enregistrements d'un service, éventuellement filtrés.
     *
     * @param string $serviceName Nom du service concerné, ex: 'individu'
     * @param array  $filters     Si $filters['source_code'] existe, seul cet enregistrement est importé
     */
    public function fetchRows($serviceName, array $filters = [])
    {
        $sourceCode = null;
        if (isset($filters['source_code'])) {
            $sourceCode = $this->normalizeSourceCode($filters['source_code']);
            $filters = []; // NB: raz des filtres
        }

        $startDate = date_create();
        $debut = microtime(true);

        $this->dbService->setServiceName($serviceName);
        $this->dbService->setEtablissement($this->etablissement);
        $this->dbService->beginTransaction();

        if ($sourceCode) {
            $this->dbService->clear(['id' => $sourceCode]);
        } else {
            $this->dbService->clear($filters);
        }

        $this->callService->setConfig($this->getConfigForEtablissement());
        $apiFilters = $this->prepareFiltersForAPIRequest($filters);
        $jsonEntitiesCount = 0;
        $page = 1;
        $pageCount = 0;
        do {
            $uri = $serviceName;
            if ($sourceCode) {
                $uri .= '/' . $sourceCode;
            } else {
                $params = array_merge($apiFilters, ['page' => $page]);
                if (count($params) > 0) {
                    $uri .= '?' . http_build_query($params);
                }
            }

            $_deb = microtime(true);
            $json = $this->callService->get($uri);
            $_fin = microtime(true);
            $this->logger->info(sprintf("Interrogation du WS : %s en %f s.", $uri, $_fin - $_deb));

            if ($sourceCode) {
                $jsonEntities = [$json];
            } else {
                $pageCount = $json->page_count; // NB: le même nombre total de pages est retourné à chaque requête
                $jsonName = str_replace("-", "_", $serviceName);
                $jsonEntities = $json->{'_embedded'}->{$jsonName};
                $page++;
            }

            $_deb = microtime(true);
            $this->dbService->save($jsonEntities);
            $_fin = microtime(true);
            $this->logger->info(sprintf("  Enregistrement dans la table temporaire : %d lignes en %f s.", count($jsonEntities), $_fin - $_deb));

            $jsonEntitiesCount += count($jsonEntities);
            unset($jsonEntities);
        }
        while ($page <= $pageCount);
        $_fin = microtime(true);

        $this->dbService->commit();

        $this->logger->info(sprintf("} %f s.", $_fin - $debut));
        $this->dbService->insertLog($serviceName . ($sourceCode ? "[$sourceCode]" : ''), $startDate, $_fin - $debut, $uri, 'OK');
        $this->dbService->commit();
    }

    /**
     * @param string $sourceCode
     * @return string
     */
    private function normalizeSourceCode($sourceCode)
    {
        if (! $sourceCode) {
            return $sourceCode;
        }

        try {
            $sourceCode = $this->sourceCodeStringHelper->removePrefixFrom($sourceCode);
        } catch (RuntimeException $e) {
            // le source code n'est pas préfixé, tant mieux.
        }

        return $sourceCode;
    }

    /**
     * Retourne la config à transmettre au service d'appel du WS pour l'établissement courant.
     *
     * @return array
     */
    private function getConfigForEtablissement()
    {
        $codeEtablissement = $this->etablissement->getCode();

        try {
            Assertion::keyIsset($this->config, $codeEtablissement);
        } catch (AssertionFailedException $e) {
            throw new RuntimeException("Aucune clé de config ne correspond au code établissement '{$codeEtablissement}'.", null, $e);
        }

        return $this->config[$codeEtablissement];
    }

    /**
     * @param array $filters
     * @return array
     */
    private function prepareFiltersForAPIRequest(array $filters)
    {
        if (empty($filters)) {
            return $filters;
        }

        $filtersToMerge = [];

        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'these':
                    /** @var These $these */
                    $these = $value;
                    $filtersToMerge['these_id'] = $this->normalizeSourceCode($these->getSourceCode());
                    // NB: les WS ne traitent que des sources codes.
                    break;
                default:
                    break;
            }
        }

        return array_merge($filters, $filtersToMerge);
    }
}