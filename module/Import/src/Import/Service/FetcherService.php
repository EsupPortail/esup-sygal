<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\SourceCodeStringHelper;
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
     * Demande au WS d'import un enregistrement particulier d'un service.
     *
     * @param string $serviceName
     * @param string $sourceCode Source code de l'enregistrement à importer
     */
    public function fetchRow($serviceName, $sourceCode)
    {
        $sourceCode = $this->normalizeSourceCode($sourceCode);

        $this->logger->info(sprintf("Import: service %s[%s] {", $serviceName, $sourceCode));

        $debut = microtime(true);
        $startDate = date_create();

        $uri = $serviceName . "/" . $sourceCode;
        $this->callService->setConfig($this->getConfigForEtablissement());
        $jsonEntity = $this->callService->get($uri);
        $_fin = microtime(true);
        $this->logger->info(sprintf("- interrogation du WS '%s' en %.2f secondes", $serviceName, $_fin - $debut));

        $_deb = microtime(true);
        $this->dbService->setServiceName($serviceName);
        $this->dbService->setEtablissement($this->etablissement);
        $this->dbService->clear(['id' => $sourceCode]);
        $this->dbService->save([$jsonEntity]);
        $this->dbService->commit();
        $_fin = microtime(true);
        $this->logger->info(sprintf("- enregistrement en BDD en %.2f secondes.", $_fin - $_deb));

        $this->logger->info(sprintf("} %.2f secondes.", $_fin - $debut));
        $this->dbService->insertLog($serviceName . ($sourceCode ? "[$sourceCode]" : ''), $startDate, $_fin - $debut, $uri, 'OK');
        $this->dbService->commit();
    }

    /**
     * Demande au WS d'import tous les enregistrements d'un service, éventuellement filtrés.
     *
     * @param string $serviceName
     * @param array  $filters
     */
    public function fetchRows($serviceName, array $filters = [])
    {
        $this->logger->info(sprintf("Import: service '%s' {", $serviceName));

        $startDate = date_create();
        $debut = microtime(true);

        $this->callService->setConfig($this->getConfigForEtablissement());
        $apiFilters = $this->prepareFiltersForAPIRequest($filters);
        $jsonEntities = [];
        $page = 1;
        do {
            $params = array_merge($apiFilters, ['page' => $page]);
            $uri = $serviceName;
            if (count($params) > 0) {
                $uri .= '?' . http_build_query($params);
            }

            $_deb = microtime(true);
            $json = $this->callService->get($uri);
            $_fin = microtime(true);
            $this->logger->debug(sprintf("  ('%s' en %.2f secondes.)", $uri, $_fin - $_deb));

            $pageCount = $json->page_count; // NB: même valeur retournée à chaque requête (nombre total de pages)
            $jsonName = str_replace("-", "_", $serviceName);
            $jsonCollection = $json->{'_embedded'}->{$jsonName};
            $jsonEntities = array_merge($jsonEntities, $jsonCollection);
            $page++;
        }
        while ($page <= $pageCount);
        $_fin = microtime(true);
        $this->logger->info(sprintf("- interrogation du WS '%s' en %.2f secondes", $serviceName, $_fin - $debut));

        $_deb = microtime(true);
        $this->dbService->setServiceName($serviceName);
        $this->dbService->setEtablissement($this->etablissement);
        $this->dbService->clear($filters);
        $this->dbService->save($jsonEntities);
        $this->dbService->commit();
        $_fin = microtime(true);
        $this->logger->info(sprintf("- enregistrement en BDD de %d lignes en %.2f secondes.", count($jsonEntities), $_fin - $_deb));

        $this->logger->info(sprintf("} %.2f secondes.", $_fin - $debut));
        $this->dbService->insertLog($serviceName, $startDate, $_fin - $debut, $uri, 'OK');
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