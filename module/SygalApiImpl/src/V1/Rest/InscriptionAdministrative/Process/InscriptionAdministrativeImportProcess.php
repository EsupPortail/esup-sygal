<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Process;

use Application\Entity\Db\Repository\SourceRepositoryAwareTrait;
use Application\SourceCodeStringHelperAwareTrait;
use Exception;
use Import\Filter\PrefixEtabColumnValueFilter;
use InvalidArgumentException;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use stdClass;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\InscriptionAdministrativeApprenantExtractor;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\InscriptionAdministrativeExtractor;
use SygalApiImpl\Event\InscriptionAdministrativeImportEvent;
use SygalApiImpl\Exception\ErrorDuringImportException;
use SygalApiImpl\Process\AbstractProcess;
use UnicaenDbImport\Config\ConfigException;
use UnicaenDbImport\Connection\NoConnection;
use UnicaenDbImport\Domain\Destination;
use UnicaenDbImport\Domain\Import;
use UnicaenDbImport\Domain\Result;
use UnicaenDbImport\Domain\Source;
use UnicaenDbImport\Domain\Synchro;
use UnicaenDbImport\Filter\ColumnValue\ConcatColumnValueFilter;

class InscriptionAdministrativeImportProcess extends AbstractProcess implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use SourceCodeStringHelperAwareTrait;
    use SourceRepositoryAwareTrait;

    protected InscriptionAdministrativeExtractor $inscriptionAdministrativeExtractor;
    protected InscriptionAdministrativeApprenantExtractor $inscriptionAdministrativeApprenantExtractor;

    protected PrefixEtabColumnValueFilter $prefixEtabColumnValueFilter;

    public function __construct()
    {
        $this->setInscriptionAdministrativeExtractor(new InscriptionAdministrativeExtractor());
        $this->setInscriptionAdministrativeApprenantExtractor(new InscriptionAdministrativeApprenantExtractor());
        $this->setPrefixEtabColumnValueFilter(new PrefixEtabColumnValueFilter());
    }

    public function setInscriptionAdministrativeExtractor(InscriptionAdministrativeExtractor $inscriptionAdministrativeExtractor): void
    {
        $this->inscriptionAdministrativeExtractor = $inscriptionAdministrativeExtractor;
    }

    public function setInscriptionAdministrativeApprenantExtractor(InscriptionAdministrativeApprenantExtractor $inscriptionAdministrativeApprenantExtractor): void
    {
        $this->inscriptionAdministrativeApprenantExtractor = $inscriptionAdministrativeApprenantExtractor;
    }

    public function setPrefixEtabColumnValueFilter(PrefixEtabColumnValueFilter $prefixEtabColumnValueFilter): void
    {
        $this->prefixEtabColumnValueFilter = $prefixEtabColumnValueFilter;
    }

    /**
     * @throws \SygalApiImpl\Exception\ErrorDuringImportException
     */
    public function import(stdClass $data): void
    {
        $this->beginTransaction();
        try {
            $iaSourceCode = $this->importInscriptionAdministrative($data);
            $this->importInscriptionAdministrativeApprenant($data);
        } catch (ErrorDuringImportException $e) {
            // erreur gérée durant un import : commit nécessaire pour enregistrer les logs en bdd.
            $this->commit();
            throw $e;
        } catch (Exception $e) {
            // erreur grave imprévue durant l'import (ex : erreur d'écriture dans la table de log) : rollback requis.
            $this->rollback($e);
            throw $e;
        }
        // tout s'est bien passé, commit.
        $this->commit();

        // déclenchement d'un événement signalant la fin du traitement des données reçues
        $event = new InscriptionAdministrativeImportEvent();
        $event->setInscriptionAdministrativeSourceCode($iaSourceCode);
        $this->events->triggerEvent($event);
    }

    /**
     * Configure et lance l'import de l'IA à partir des données reçues.
     * Retourne le source_code de l'IA importée.
     *
     * @return string source_code de l'IA importée
     *
     * @throws \SygalApiImpl\Exception\ErrorDuringImportException Erreur durant l'import mais commit possible
     * @throws \Exception Erreur grave imprévue
     */
    private function importInscriptionAdministrative(stdClass $data): string
    {
        $preparedData = $this->inscriptionAdministrativeExtractor->extract($data);

        // NB : on cherche la Source dont proviennent les données, ce qui va permettre de fournir le code étab associé
        // pour réaliser le préfixage du code ED afin que la jointure dans la vue SRC se fasse avec une ED déjà en BDD.
        // Sans ça, pour que la jointure se fasse on serait obligé d'avoir en BDD toutes les ED dans la même Source
        // que les données importées (càd Pégase) ; on n'a pas envie de faire ça pour l'instant.
        $sourceId = $this->inscriptionAdministrativeExtractor->extractSourceId($data);
        $source = $this->sourceRepository->findOneByCode($sourceId);
        if ($source === null) {
            throw new InvalidArgumentException("La source '$sourceId' spécifiée dans les données est introuvable");
        }
        $etabSourceCode = $source->getEtablissement()->getSourceCode();

        $import = $this->createImportInscriptionAdministrative($preparedData, $etabSourceCode);
        $result = $this->runImport($import);
        $this->handleResult($result, "l'import de l'inscription administrative");

        $synchro = $this->createSynchroInscriptionAdministrative($sourceId);
        $result = $this->runSynchro($synchro);
        $this->handleResult($result, "la synchro de l'inscription administrative");

        return $this->inscriptionAdministrativeExtractor->extractSourceCode($data);
    }

    /**
     * @throws \SygalApiImpl\Exception\ErrorDuringImportException Erreur durant l'import mais commit possible
     * @throws \Exception Erreur grave imprévue
     */
    private function importInscriptionAdministrativeApprenant(stdClass $data): void
    {
        $preparedData = $this->inscriptionAdministrativeApprenantExtractor->extract($data);
        $sourceId = $preparedData['source_id'];

        $import = $this->createImportInscriptionAdministrativeApprenant($preparedData);
        $result = $this->runImport($import);
        $this->handleResult($result, "l'import de l'inscription administrative apprenant");

        $synchro = $this->createSynchroInscriptionAdministrativeApprenant($sourceId);
        $result = $this->runSynchro($synchro);
        $this->handleResult($result, "la synchro de l'inscription administrative apprenant");
    }

    /**
     * @throws \SygalApiImpl\Exception\ErrorDuringImportException
     */
    private function handleResult(Result $result, string $intituleAction): void
    {
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de $intituleAction : " . $e->getMessage(), 0, $e
            );
        } elseif ($result->hasExceptionInResults()) {
            throw new ErrorDuringImportException(
                "Une ou plusieurs opérations ont échoué lors de $intituleAction : " . $result->toString()
            );
        }
    }

    private function createImportInscriptionAdministrative(array $data, string $codeEtablissement): Import
    {
        // On préfixe la valeur de 'ecole_doct_id' par le code établissement pour que la jointure dans
        // avec la table ECOLE_DOCT (dans SRC_INSCRIPTION_ADMINISTRATIVE) se fasse. Cela évite d'avoir à créer
        // toutes les ED possibles dans la Source Pégase.
        $this->prefixEtabColumnValueFilter->setParams([
            'column' => 'ecole_doct_id',
            PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $codeEtablissement,
        ]);

        try {
            $source = Source::fromConfig([
                'name' => 'api',
                'connection' => new NoConnection([$data]),
                'select' => 'xxxxx',
                'source_code_column' => 'source_code',
                'column_value_filter' => [
                    new ConcatColumnValueFilter([
                        'column' => 'source_code', // colonne à transformer
                        'columns' => ['source_id', 'source_code'], // colonnes dont les valeurs seront concaténées
                        'separator' => $this->sourceCodeStringHelper->getSeparator(),
                    ]),
                    $this->prefixEtabColumnValueFilter,
                ],
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'tmp_inscription_administrative',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'id_strategy' => null,
                'id_sequence' => null,
            ]);

            return Import::fromConfig([
                'name' => 'api_import_inscription',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__ . " : " . $e->getMessage(), 0, $e);
        }
    }

    private function createImportInscriptionAdministrativeApprenant(array $data): Import
    {
        try {
            $source = Source::fromConfig([
                'name' => 'api',
                'connection' => new NoConnection([$data]),
                'select' => 'xxxxx',
                'source_code_column' => 'source_code',
                'column_value_filter' => [
                    new ConcatColumnValueFilter([
                        'column' => 'source_code', // colonne à transformer
                        'columns' => ['source_id', 'source_code'], // colonnes dont les valeurs seront concaténées
                        'separator' => $this->sourceCodeStringHelper->getSeparator(),
                    ]),
                    new ConcatColumnValueFilter([
                        'column' => 'inscription_administrative_id', // colonne à transformer
                        'columns' => ['source_id', 'inscription_administrative_id'], // colonnes dont les valeurs seront concaténées
                        'separator' => $this->sourceCodeStringHelper->getSeparator(),
                    ]),
                ],
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'tmp_inscription_administrative_apprenant',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'id_strategy' => null,
                'id_sequence' => null,
            ]);

            return Import::fromConfig([
                'name' => 'api_import_inscription_administrative_apprenant',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__ . " : " . $e->getMessage(), 0, $e);
        }
    }

    private function createSynchroInscriptionAdministrative(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

        try {
            $source = Source::fromConfig([
                'name' => 'Application',
                'code' => 'app',
                'table' => 'src_inscription_administrative',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'inscription_administrative',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'where' => $where,
                'id_strategy' => 'SEQUENCE',
                'id_sequence' => null,
            ]);

            return Synchro::fromConfig([
                'name' => 'api_synchro_inscription',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__ . " : " . $e->getMessage(), 0, $e);
        }
    }

    private function createSynchroInscriptionAdministrativeApprenant(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

        try {
            $source = Source::fromConfig([
                'name' => 'Application',
                'code' => 'app',
                'table' => 'src_inscription_administrative_apprenant',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'inscription_administrative_apprenant',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'where' => $where,
                'id_strategy' => 'SEQUENCE',
                'id_sequence' => null,
            ]);

            return Synchro::fromConfig([
                'name' => 'api_synchro_inscription_administrative_apprenant',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__ . " : " . $e->getMessage(), 0, $e);
        }
    }
}