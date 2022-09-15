<?php

namespace Import\Controller;

use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Doctrine\ORM\EntityManager;
use Import\Service\Traits\ImportServiceAwareTrait;
use Interop\Container\ContainerInterface;
use Laminas\Log\Filter\Priority;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Structure\Service\Etablissement\EtablissementServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ImportController extends AbstractActionController
{
    use EntityManagerAwareTrait;
    use ImportServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EtablissementServiceAwareTrait;
    use SourceCodeStringHelperAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array $config [ 'CODE_ETABLISSEMENT' => [...] ]
     */
    protected $config;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return ViewModel
     * @deprecated À réécrire pour unicane/db-import
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
     * @deprecated À réécrire pour unicane/db-import
     */
    public function updateTheseConsoleAction()
    {
        $id = $this->params('id');
        $emName = $this->params('em', 'orm_default');
        $verbose = (bool) $this->params('verbose', 0);

        /** @var These $these */
        $these = $this->theseService->getRepository()->find($id);

        $this->setLoggerStream('php://output', $verbose);

        /** @var EntityManager $entityManager */
        $entityManager = $this->container->get("doctrine.entitymanager.$emName");

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
