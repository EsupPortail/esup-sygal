<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade;

use Exception;
use Import\Filter\PrefixEtabColumnValueFilter;
use InvalidArgumentException;
use stdClass;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\DoctorantExtractor;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\IndividuExtractor;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\InscriptionExtractor;
use SygalApiImpl\V1\Exception\ErrorDuringImportException;
use SygalApiImpl\V1\Facade\AbstractImportFacade;
use UnicaenDbImport\Config\ConfigException;
use UnicaenDbImport\Connection\ApiConnection;
use UnicaenDbImport\Domain\Destination;
use UnicaenDbImport\Domain\Import;
use UnicaenDbImport\Domain\Operation;
use UnicaenDbImport\Domain\Source;
use UnicaenDbImport\Domain\Synchro;

class ImportFacade extends AbstractImportFacade
{
    protected IndividuExtractor $individuExtractor;
    protected DoctorantExtractor $doctorantExtractor;
    protected InscriptionExtractor $inscriptionExtractor;

    public function __construct()
    {
        $this->individuExtractor = new IndividuExtractor();
        $this->doctorantExtractor = new DoctorantExtractor();
        $this->inscriptionExtractor = new InscriptionExtractor();
    }

    public function import(stdClass $data)
    {
        $this->beginTransaction();
        try {
            $this->importIndividu($data);
            $this->importDoctorant($data);
            $this->importInscription($data);
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
    }

    /**
     * @throws \SygalApiImpl\V1\Exception\ErrorDuringImportException Erreur durant l'import mais commit possible
     * @throws \Exception Erreur grave imprévue
     */
    private function importIndividu(stdClass $data)
    {
        $preparedData = $this->individuExtractor->extract($data);
        $import = $this->createImportIndividu($preparedData);
        $result = $this->runImport($import);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de l'import de l'individu : " . $e->getMessage(), null, $e
            );
        }

        $sourceId = $preparedData['source_id'];
        $synchro = $this->createSynchroIndividu($sourceId);
        $result = $this->runSynchro($synchro);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de la synchro de l'individu : " . $e->getMessage(), null, $e
            );
        }
    }

    /**
     * @throws \SygalApiImpl\V1\Exception\ErrorDuringImportException Erreur durant l'import mais commit possible
     * @throws \Exception Erreur grave imprévue
     */
    private function importDoctorant(stdClass $data)
    {
        $preparedData = $this->doctorantExtractor->extract($data);
        $import = $this->createImportDoctorant($preparedData);
        $result = $this->runImport($import);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de l'import du doctorant : " . $e->getMessage(), null, $e
            );
        }

        $sourceId = $preparedData['source_id'];
        $synchro = $this->createSynchroDoctorant($sourceId);
        $result = $this->runSynchro($synchro);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de la synchro du doctorant : " . $e->getMessage(), null, $e
            );
        }
    }

    /**
     * @throws \SygalApiImpl\V1\Exception\ErrorDuringImportException Erreur durant l'import mais commit possible
     * @throws \Exception Erreur grave imprévue
     */
    private function importInscription(stdClass $data)
    {
        $preparedData = $this->inscriptionExtractor->extract($data);
        $import = $this->createImportInscription($preparedData);
        $result = $this->runImport($import);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de l'import de l'inscription : " . $e->getMessage(), null, $e
            );
        }

        $sourceId = $preparedData['source_id'];
        $synchro = $this->createSynchroInscription($sourceId);
        $result = $this->runSynchro($synchro);
        if ($e = $result->getFailureException()) {
            throw new ErrorDuringImportException(
                "Une erreur est survenue lors de la synchro de l'inscription : " . $e->getMessage(), null, $e
            );
        }
    }

    private function createImportIndividu(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            //'source_id', // pas le source_id !
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        try {
            $source = Source::fromConfig([
                'name' => 'api',
                'connection' => new ApiConnection(),
                'select' => 'xxxxx',
                'source_code_column' => 'source_code',
                'column_value_filter' => $filter,
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'tmp_individu',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'id_sequence' => false,
            ]);

            $source->setData([$data]);

            return Import::fromConfig([
                'name' => 'api_import_individu',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }

    private function createSynchroIndividu(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

        try {
            $source = Source::fromConfig([
                'name' => 'Application',
                'code' => 'app',
                'table' => 'src_individu',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'individu',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'where' => $where,
            ]);

            return Synchro::fromConfig([
                'name' => 'api_synchro_individu',
                'source' => $source,
                'destination' => $destination,
                'operations' => [
                    Operation::OPERATION_INSERT,
                    Operation::OPERATION_UPDATE,
                    Operation::OPERATION_UNDELETE,
                ],
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }

    private function createImportDoctorant(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            //'source_id', // pas le source_id !
            'individu_id',
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        try {
            $source = Source::fromConfig([
                'name' => 'api',
                'connection' => new ApiConnection(),
                'select' => 'xxxxx',
                'source_code_column' => 'source_code',
                'column_value_filter' => $filter,
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'tmp_doctorant',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'id_sequence' => false,
            ]);
            $source->setData([$data]);
            return Import::fromConfig([
                'name' => 'api_import_doctorant',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }

    private function createSynchroDoctorant(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

        try {
            $source = Source::fromConfig([
                'name' => 'Application',
                'code' => 'app',
                'table' => 'SRC_DOCTORANT',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'DOCTORANT',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'where' => $where,
            ]);
            return Synchro::fromConfig([
                'name' => 'api_synchro_doctorant',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }

    private function createImportInscription(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            //'source_id', // pas le source_id !
            'doctorant_id',
            'ecole_doctorale_id',
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        try {
            $source = Source::fromConfig([
                'name' => 'api',
                'connection' => new ApiConnection(),
                'select' => 'xxxxx',
                'source_code_column' => 'source_code',
                'column_value_filter' => $filter,
            ]);
            $destination = Destination::fromConfig([
                'name' => 'Application',
                'table' => 'tmp_inscription_administrative',
                'connection' => $this->destinationConnection,
                'source_code_column' => 'source_code',
                'id_sequence' => false,
            ]);
            $source->setData([$data]);
            return Import::fromConfig([
                'name' => 'api_import_inscription',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }

    private function createSynchroInscription(string $sourceId): Synchro
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
            ]);
            return Synchro::fromConfig([
                'name' => 'api_synchro_inscription',
                'source' => $source,
                'destination' => $destination,
            ]);
        } catch (ConfigException $e) {
            throw new InvalidArgumentException("Mauvaise config dans " . __METHOD__, null, $e);
        }
    }
}