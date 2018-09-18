<?php
/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \UnicaenCode\Controller\Controller
 * @var $viewName   string
 */

use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use UnicaenOracle\Service\DataService;
use UnicaenOracle\Service\SchemaService;

$destDir = __DIR__ . '/';

/** @var SchemaService $schemaService */
$schemaService = $controller->getServiceLocator()->get(SchemaService::class);
/** @var DataService $dataService */
$dataService = $controller->getServiceLocator()->get(DataService::class);

/** @var Connection $srcSchemaConn */
$srcSchemaConn = $controller->getServiceLocator()->get('doctrine.connection.orm_default');

$srcSchema = 'SYGAL';
$dstSchema = 'SYGAL_TEST';

echo "<h1>DB Schema files generator</h1>";

/**
 * Schema clearing.
 */
$outputFilePath = $destDir . "/oracle-clear-schema-$dstSchema.sql";
echo "<h2>Schema clearing</h2>";
try {
    $schemaService->createSchemaClearingScriptFile($srcSchemaConn, $dstSchema, $outputFilePath);
    var_dump($outputFilePath);
} catch (NoResultException $e) {
    var_dump("Impossible de générer le script de vidage du schéma '$dstSchema'. 
    Peut-être est-il déjà vide ou alors le schéma n'est pas accessible avec la connexion utilisée.");
}

/**
 * Schema creation.
 */
$outputFilePath = $destDir . "/oracle-generate-schema-$dstSchema-from-$srcSchema.sql";
$schemaService->createSchemaCreationScriptFile($srcSchemaConn, $srcSchema, $dstSchema, $outputFilePath);
echo "<h2>Schema creation</h2>";
var_dump($outputFilePath);

/**
 * Data inserts.
 */
$tableNames = [
    'ACTEUR',
    'ATTESTATION',
    'CATEGORIE_PRIVILEGE',
    'DIFFUSION',
    'DOCTORANT',
    'DOCTORANT_COMPL',
    'DOMAINE_SCIENTIFIQUE',
    'ECOLE_DOCT',
    'ETABLISSEMENT',
    'ETABLISSEMENT_RATTACH',
    'FAQ',
    'FICHIER',
    'IMPORT_NOTIF',
    'IMPORT_OBS_NOTIF',
    'IMPORT_OBS_RESULT_NOTIF',
    'IMPORT_OBSERV',
    'IMPORT_OBSERV_RESULT',
    'INDIVIDU',
    'INDIVIDU_ROLE',
    'MAIL_CONFIRMATION',
    'METADONNEE_THESE',
    'NATURE_FICHIER',
    'NOTIF',
    'NOTIF_RESULT',
    'PRIVILEGE',
    'RDV_BU',
    'ROLE',
    'ROLE_MODELE',
    'ROLE_PRIVILEGE',
    'ROLE_PRIVILEGE_MODELE',
    'SOURCE',
    'STRUCTURE',
    'STRUCTURE_SUBSTIT',
    'THESE',
    'TYPE_STRUCTURE',
    'TYPE_VALIDATION',
    'UNITE_DOMAINE_LINKER',
    'UNITE_RECH',
    'UTILISATEUR',
    'VALIDATION',
    'VALIDITE_FICHIER',
    'VARIABLE',
    'VERSION_FICHIER',
    'WF_ETAPE',
];
$outputFilePathTemplate = $destDir . "/oracle-data-insert-from-$srcSchema.%s-into-$dstSchema.sql";
$outputFilePaths = $dataService->createDataInsertsScriptFile($srcSchemaConn, $dstSchema, $tableNames, $outputFilePathTemplate);
echo "<h2>Data inserts</h2>";
var_dump($outputFilePaths);



