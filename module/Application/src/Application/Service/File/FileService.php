<?php

namespace Application\Service\File;

use Application\Util;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;

class FileService
{
    /**
     * @var string
     */
    private $rootDirectoryPath;

    /**
     * @param string $rootDirectoryPath
     */
    public function setRootDirectoryPath($rootDirectoryPath)
    {
        $this->rootDirectoryPath = $rootDirectoryPath;
    }

    /**
     * @return string
     */
    public function getRootDirectoryPath()
    {
        return $this->rootDirectoryPath;
    }

    public function getCompleteFilepath($relativeFilepath)
    {
        return $this->rootDirectoryPath . '/' . $relativeFilepath;
    }

    /**
     * @param Response $response
     * @param string   $fileContent
     * @param int|null $cacheMaxAge En secondes, ex: 60*60*24 = 86400 s = 1 jour
     * @return Response
     */
    public function createResponseForFileContent(Response $response, $fileContent, $cacheMaxAge = null)
    {
        $response->setContent($fileContent);

        $headers = $response->getHeaders();
        $headers
            ->addHeaderLine('Content-Transfer-Encoding', "binary")
            ->addHeaderLine('Content-Type', "image/png")
            ->addHeaderLine('Content-length', strlen($fileContent));

        if ($cacheMaxAge === null) {
            $headers
                ->addHeaderLine('Cache-Control', "no-cache, no-store, must-revalidate")
                ->addHeaderLine('Pragma', 'no-cache');
        }
        else {
            // autorisation de la mise en cache de l'image par le client
            $headers
                ->addHeaderLine('Cache-Control', "private, max-age=$cacheMaxAge")
                ->addHeaderLine('Pragma', 'private')// tout sauf 'no-cache'
                ->addHeaderLine('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheMaxAge));
        }

        return $response;
    }
}