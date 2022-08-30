<?php

namespace Fichier\Service\Storage\Adapter;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Fichier\Service\Storage\Adapter\Exception\StorageAdapterException;
use Webmozart\Assert\Assert;

class S3StorageAdapter extends AbstractStorageAdapter
{
    protected string $pathSeparator = '--';

    private ?array $clientConfig;
    private ?S3Client $client = null;

    public function setConfig(array $config)
    {
        parent::setConfig($config);

        $config = $this->config;

        Assert::keyExists($this->config, $k = 'client', "Clé $k introuvable dans la config de l'adapter");
        $this->clientConfig = $config['client'];

        Assert::keyExists($this->clientConfig, $k = 'end_point', "Clé $k introuvable dans la config du client");
        Assert::keyExists($this->clientConfig, $k = 'access_key', "Clé $k introuvable dans la config du client");
        Assert::keyExists($this->clientConfig, $k = 'secret_key', "Clé $k introuvable dans la config du client");
    }

    public function setClient(S3Client $client): void
    {
        $this->client = $client;
    }

    protected function getClient(): S3Client
    {
        if ($this->client === null) {
            $this->client = $this->createS3Client();
        }

        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function computeDirectoryPath(string ...$pathParts): string
    {
        $path = parent::computeDirectoryPath(...$pathParts);

        // pour que le chemin soit compatible S3
        return strtolower(strtr($path, "_' ", '---'));
    }

    /**
     * @throws \Fichier\Service\Storage\Adapter\Exception\StorageAdapterException
     */
    protected function createBucket(string $name)
    {
        $client = $this->getClient();

        try {
            $client->createBucket(['Bucket' => $name]);
            $client->waitUntil('BucketExists', ['Bucket' => $name]);
        } catch (S3Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw new StorageAdapterException(
                "Erreur rencontrée lors de la création du bucket '$name'", null, $e);
        }
    }

//    protected function listBuckets()
//    {
//        $client = $this->getClient();
//
//        $listResponse = $client->listBuckets();
//        $buckets = $listResponse['Buckets'];
//        foreach ($buckets as $bucket) {
//            echo $bucket['Name'] . "\t" . $bucket['CreationDate'] . "\n";
//        }
//    }

    /**
     * @inheritDoc
     */
    public function saveFileContent(string $fileContent, string $toDirPath, string $toFileName)
    {
        $client = $this->getClient();

        if (!$client->doesBucketExist($toDirPath)) {
            $this->createBucket($toDirPath);
        }

        try {
            $client->putObject([
                'Bucket' => $toDirPath,
                'Key' => $toFileName,
                'Body' => $fileContent,
            ]);
        } catch (S3Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw new StorageAdapterException(
                "Erreur rencontrée lors de l'upload de '$toFileName' dans le bucket '$toDirPath'", null, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteFile(string $dirPath, string $fileName)
    {
        $client = $this->getClient();

        if (!$client->doesObjectExist($dirPath, $fileName)) {
            throw (new StorageAdapterException("Fichier '$fileName' introuvable dans le bucket '$dirPath'"))
                ->setDirPath($dirPath)
                ->setFileName($fileName);
        }

        try {
            $client->deleteObject(['Bucket' => $dirPath, 'Key' => $fileName]);
        } catch (S3Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw (new StorageAdapterException(
                "Erreur rencontrée lors la suppression de '$fileName' dans le bucket '$dirPath'", null, $e))
                ->setDirPath($dirPath)
                ->setFileName($fileName);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFileContent(string $dirPath, string $fileName): string
    {
        $client = $this->getClient();

        if (!$client->doesObjectExist($dirPath, $fileName)) {
            throw (new StorageAdapterException("Fichier '$fileName' introuvable dans le bucket '$dirPath'"))
                ->setDirPath($dirPath)
                ->setFileName($fileName);
        }

        try {
            $object = $client->getObject(['Bucket' => $dirPath, 'Key' => $fileName]);
            $content = $object['Body']->getContents();
        } catch (S3Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw new StorageAdapterException(
                "Erreur rencontrée lors l'obtention de '$fileName' dans le bucket '$dirPath'", null, $e);
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function saveToFilesystem(string $fromDirPath, string $fromFileName, string $toFilesystemPath)
    {
        $content = $this->getFileContent($fromDirPath, $fromFileName);

        file_put_contents($toFilesystemPath, $content);
    }

    private function createS3Client(): S3Client
    {
        $endPoint = $this->clientConfig['end_point'];
        $accessKey = $this->clientConfig['access_key'];
        $secretKey = $this->clientConfig['secret_key'];

        // Instantiate the S3 class and point it at the desired host
        return new S3Client([
            'region' => 'us-east-1',
//            'version' => '2006-03-01',
            'version' => 'latest',
            'endpoint' => $endPoint,
//            'http' => [
//                'verify' => false,
//            ],
//            'debug' => true,
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
            // Set the S3 class to use objects.dreamhost.com/bucket
            // instead of bucket.objects.dreamhost.com
            'use_path_style_endpoint' => true,
        ]);
    }
}