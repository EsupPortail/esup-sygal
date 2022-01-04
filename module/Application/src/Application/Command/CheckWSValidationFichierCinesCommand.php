<?php

namespace Application\Command;

use InvalidArgumentException;
use UnicaenApp\Exception\RuntimeException;

/**
 * TODO : si encore utilisée, transformer cette classe en ShellCommand (ex: TestArchivabiliteShellCommand).
 */
class CheckWSValidationFichierCinesCommand
{
    const URL = 'https://facile.cines.fr/xml';

    /**
     * Exemples de réponses normales :
     *      "RESPONSE: OK - 893 ms|Response=893ms;1000;5000;0"
     *      "RESPONSE: CRITICAL - 5493 ms|Response=5493ms;1000;5000;0"
     */
    const RESPONSE_FORMAT = '/^RESPONSE: (OK|WARNING|CRITICAL) - (\d+) ms(.+)$/';

    /**
     * Exemple de réponse avec erreur :
     *      "RESPONSE: UNKNOWN - ERROR: /bin/nc does does not exist|Response=ms;1000;5000;0"
     */
    const ERROR_FORMAT = '/ERROR: (.+)\|Response=(.+)/';

    const STATUS_OK = 'OK';
    const STATUS_WARNING = 'WARNING';
    const STATUS_CRITICAL = 'CRITICAL';

    const STATUSES = [
        self::STATUS_OK       => 0,
        self::STATUS_WARNING  => 1,
        self::STATUS_CRITICAL => 2,
    ];

    /**
     * @var string
     */
    protected $scriptFilePath;

    /**
     * @var string
     */
    protected $result;

    /**
     * @var array
     */
    protected $resultMatches;

    /**
     * @param string $scriptFilePath
     */
    public function __construct(string $scriptFilePath)
    {
        $this->scriptFilePath = $scriptFilePath;
    }

    /**
     * Exécute la commande.
     */
    public function execute()
    {
        $args = sprintf("-w 1000 -c 5000 -u %s", self::URL);
        $command = sprintf("sh %s %s", $this->scriptFilePath, $args);

        $runner = new ShellCommandRunner();
        $runner->setCommandAsString($command);
        $this->checkPrerequisites();
        echo sprintf("Lancement de la commande: %s\n", $command);
        $result = $runner->runCommand();
        $this->result = implode(PHP_EOL, $result->getOutput());
        $this->parseResult();
    }

    private function checkPrerequisites()
    {
        $dir = dirname($this->scriptFilePath);
        $samplePdfFilePath = $dir . '/sample.pdf';

        if (! is_readable($samplePdfFilePath)) {
            throw new InvalidArgumentException(
                "Le fichier échantillon 'sample.pdf' doit être présent dans le répertoire " . $dir);
        }
    }

    private function parseResult()
    {
        if (preg_match($pattern = self::ERROR_FORMAT, $this->result, $matches)) {
            throw new RuntimeException(
                "La réponse du script de test du web service de validation indique qu'il a rencontré l'erreur '$matches[1]' : " .
                PHP_EOL . $this->result);
        }

        if (! preg_match($pattern = self::RESPONSE_FORMAT, $this->result, $matches)) {
            throw new RuntimeException(
                "La réponse du script de test du web service de validation n'est pas au format '$pattern' attendu : " .
                PHP_EOL . $this->result);
        }

        $this->resultMatches = $matches;
    }

    /**
     * @return string
     */
    public function getStatusResult()
    {
        return $this->resultMatches[1];
    }

    /**
     * @param string $worstAcceptableStatus
     * @return bool
     */
    public function getBooleanStatusResult($worstAcceptableStatus = self::STATUS_WARNING)
    {
        $worst  = self::STATUSES[$worstAcceptableStatus];
        $actual = self::STATUSES[$this->getStatusResult()];

        return $actual <= $worst;
    }

    /**
     * @return int
     */
    public function getDurationResult()
    {
        return (int) $this->resultMatches[2];
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }
}