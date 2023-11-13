<?php

namespace Application\Controller;

use Doctrine\DBAL\Connection;
use Exception;
use Laminas\Log\Filter\Priority;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\Writer\Stream;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Unicaen\Console\Controller\AbstractConsoleController;
use UnicaenApp\Service\SQL\RunSQLServiceAwareTrait;


class ConsoleController extends AbstractConsoleController
{
    use LoggerAwareTrait;
    use RunSQLServiceAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function runSQLQueryAction()
    {
        $sql = $this->params('sql');
        $connection = $this->params('connection', 'orm_default');
        $logFilepath = $this->params('logfile');

        $this->createLogger();
        $this->logger->info("### Exécution de commandes SQL ###");
        $this->logger->info(date_format(date_create(), 'd/m/Y H:i:s'));

        $this->initConnection($connection);

        $this->runSQLService->setLogger($this->logger);
        $result = $this->runSQLService->runSQLQuery($sql, $this->connection, $logFilepath);

        if ($result->isSuccess()) {
            $this->logger->info("Exécution terminée avec succès.");
        } else {
            $this->logger->info("OUPS, UNE ERREUR EST SURVENUE !");
        }

        $this->logger->info("Durée : " . $result->getDurationInSec() . " sec");

        if (! $result->isSuccess()) {
            exit(1);
        }
    }

    /**
     * @throws Exception
     */
    public function runSQLScriptAction()
    {
        $path = $this->params('path');
        $connection = $this->params('connection', 'orm_default');
        $logFilepath = $this->params('logfile');

        $this->createLogger();
        $this->logger->info("### Exécution de scripts SQL ###");
        $this->logger->info(date_format(date_create(), 'd/m/Y H:i:s'));

        $this->initConnection($connection);

        $this->runSQLService->setLogger($this->logger);
        $result = $this->runSQLService->runSQLScript($path, $this->connection, $logFilepath);

        if ($result->isSuccess()) {
            $this->logger->info("Exécution terminée avec succès.");
        } else {
            $this->logger->info("OUPS, UNE ERREUR EST SURVENUE !");
        }

        $this->logger->info("Durée : " . $result->getDurationInSec() . " sec");

        if (! $result->isSuccess()) {
            exit(1);
        }
    }

    private function createLogger()
    {
        $filter = new Priority(Logger::INFO);

        $format = '%message%'; // '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
        $formatter = new Simple($format);

        $writer = new Stream('php://output');
        $writer->addFilter($filter);
        $writer->setFormatter($formatter);

        $this->logger = new Logger();
        $this->logger->addWriter($writer);
    }

    protected function initConnection(string $name)
    {
        $serviceName = "doctrine.connection.$name";
        if (! $this->container->has($serviceName)) {
            throw new RuntimeException("Connection Doctrine introuvable : $serviceName");
        }

        $this->connection = $this->container->get($serviceName);
    }
}
