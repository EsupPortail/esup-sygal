<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade;

use Exception;
use Import\Filter\PrefixEtabColumnValueFilter;
use stdClass;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\DoctorantExtractor;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\IndividuExtractor;
use SygalApi\V1\Rest\InscriptionAdministrative\Extractor\InscriptionExtractor;
use SygalApiImpl\V1\Facade\AbstractImportFacade;
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

    /**
     * @throws \Exception
     */
    public function import(stdClass $data)
    {
        $this->destinationConnection->beginTransaction();
        try {
            $this->importIndividu($data);
            $this->importDoctorant($data);
            $this->importInscription($data);
            $this->destinationConnection->commit();
        } catch (Exception $e) {
            $this->destinationConnection->rollback();
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    private function importIndividu(stdClass $data)
    {
        $preparedData = $this->individuExtractor->extract($data);
        try {
            $import = $this->createImportIndividu($preparedData);
            $this->runImport($import);
        } catch (Exception $e) {
            throw new Exception("Une erreur est survenue lors de l'import de l'individu : " . $e->getMessage(), null, $e);
        }

        $sourceId = $preparedData['source_id'];
        try {
            $synchro = $this->createSynchroIndividu($sourceId);
            $this->runSynchro($synchro);
        } catch (Exception $e) {
            throw new Exception("Une erreur est survenue lors de la synchro de l'individu : " . $e->getMessage(), null, $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function importDoctorant(stdClass $data)
    {
        $preparedData = $this->doctorantExtractor->extract($data);
        try {
            $import = $this->createImportDoctorant($preparedData);
            $this->runImport($import);
        } catch (Exception $e) {
            throw new Exception("Une erreur est survenue lors de l'import du doctorant : " . $e->getMessage(), null, $e);
        }

        $sourceId = $preparedData['source_id'];
        try {
            $synchro = $this->createSynchroDoctorant($sourceId);
            $this->runSynchro($synchro);
        } catch (Exception $e) {
            throw new Exception("Une erreur est survenue lors de la synchro du doctorant : " . $e->getMessage(), null, $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function importInscription(stdClass $data)
    {
        $preparedData = $this->inscriptionExtractor->extract($data);
        try {
            $import = $this->createImportInscription($preparedData);
            $this->runImport($import);
        } catch (Exception $e) {
            error_log($e);
            throw new Exception("Une erreur est survenue lors de l'import de l'inscription : " . $e->getMessage(), null, $e);
        }

        $sourceId = $preparedData['source_id'];
        try {
            $synchro = $this->createSynchroInscription($sourceId);
            $this->runSynchro($synchro);
        } catch (Exception $e) {
            throw new Exception("Une erreur est survenue lors de la synchro de l'inscription : " . $e->getMessage(), null, $e);
        }
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createImportIndividu(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            //'source_id', // pas le source_id !
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        $source = Source::fromConfig([
            'name' => 'vxcvxcvxc',
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
            'name' => 'individu',
            'source' => $source,
            'destination' => $destination,
        ]);
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createSynchroIndividu(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

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
            'name' => uniqid('synchro_'),
            'source' => $source,
            'destination' => $destination,
            'operations' => [
                Operation::OPERATION_INSERT,
                Operation::OPERATION_UPDATE,
                Operation::OPERATION_UNDELETE,
            ],
        ]);
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createImportDoctorant(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            //'source_id', // pas le source_id !
            'individu_id',
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        $source = Source::fromConfig([
            'name' => 'vxcvxcvxc',
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
            'name' => 'doctorant',
            'source' => $source,
            'destination' => $destination,
        ]);
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createSynchroDoctorant(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

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
            'name' => uniqid('synchro_'),
            'source' => $source,
            'destination' => $destination,
        ]);
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createImportInscription(array $data): Import
    {
        $filter = new PrefixEtabColumnValueFilter([
            'source_code',
            'doctorant_id',
            'ecole_doctorale_id',
            //'source_id', // pas le source_id !
        ]);
        $sourceId = $data['source_id'];
        $filter->setParams([PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $sourceId]);

        $source = Source::fromConfig([
            'name' => 'vxcvxcvxc',
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
            'name' => 'inscription',
            'source' => $source,
            'destination' => $destination,
        ]);
    }

    /**
     * @throws \UnicaenDbImport\Config\ConfigException
     */
    private function createSynchroInscription(string $sourceId): Synchro
    {
        $where = "d.source_id = ( select id from source where code = '$sourceId' )";

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
            'name' => uniqid('synchro_'),
            'source' => $source,
            'destination' => $destination,
        ]);
    }
}