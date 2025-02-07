<?php

namespace Formation\Export\Xlsx\Formation;

use Application\Export\Xlsx\AbstractXlsxExport;
use Doctrine\DBAL\Exception;
use Formation\Service\Formation\FormationServiceAwareTrait;
use RuntimeException;
use Webmozart\Assert\Assert;

class FormationStatsXlsxExport extends AbstractXlsxExport
{
    use FormationServiceAwareTrait;

    const CODE = 'FORMATION_STATS_XLSX_EXPORT';

    const FORMATION_DATA_TAB = 'DATA_FORM';
    const ENQUETE_DATA_TAB = 'DATA_ENQ';

    protected string $code = self::CODE;

    private ?array $formationsIds = null;
    private ?int $sessionId = null;

    public function getHtmlDescription(): string
    {
        $templateFileName = basename($this->inputFilePath);

        return sprintf(
            "<strong>Modèle <code>%s</code></strong> <small><br>Données brutes injectées dans : %s</small>",
            $templateFileName,
            implode(', ', array_keys($this->config['data_tabs']))
        );
    }

    public function setContext(array $context): void
    {
        $this->setFormationsIds($context['formationsIds'] ?? null);
        $this->setSessionId($context['sessionId'] ?? null);
        unset($context['formationsIds']);
        unset($context['sessionId']);

        parent::setContext($context);
    }

    public function setFormationsIds(array|int|null $formationsIds): void
    {
        $this->formationsIds = $formationsIds;
    }

    public function setSessionId(int|null $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    protected function loadConfig(): void
    {
        parent::loadConfig();
        Assert::notNull($this->inputFilePath, "Un fichier source est requis pour cet export");
    }

    protected function generate(string $outputFilePath): void
    {
        // ATTENTION :
        // on décide de ne pas inclure les données "enquêtes" si plus d'1 formation est concernée,
        // à cause du volume de données potentielles.
        if ($this->formationsIds === null || is_array($this->formationsIds) && count($this->formationsIds) > 1) {
            $tabName = static::ENQUETE_DATA_TAB;
            unset($this->dataTabsConfig[$tabName]);
            $this->addMessage("Aucune donnée injectée dans l'onglet $tabName dès lors que plusieurs formations sont concernées.");
        }

        parent::generate($outputFilePath);
    }

    protected function fetchDataForTab(string $tabName): array
    {
        $viewName = $this->getViewNameForTab($tabName);
        $viewColumns = $this->getViewColumnsForTab($tabName);

        $sql = sprintf(
            "select %s from $viewName",
            implode(', ', $viewColumns)
        );

        $params = [];
        $wheres = [];
        if ($this->formationsIds !== null) {
            $in = implode(',', (array)$this->formationsIds);
            $wheres[] = "formation_id in ($in)";
        }
        if ($this->sessionId !== null) {
            $wheres[] = "session_id = :sessionId";
            $params['sessionId'] = $this->sessionId;
        }
        if ($wheres) {
            $sql .= " where " . implode(' and ', $wheres);
        }

        $conn = $this->formationService->getEntityManager()->getConnection();
        try {
            $result = $conn->executeQuery($sql, $params)->fetchAllAssociative();
        } catch (Exception $e) {
            throw new RuntimeException("Erreur survenue lors du fetch : " . $e->getMessage(), null, $e);
        }

        return $result;
    }
}