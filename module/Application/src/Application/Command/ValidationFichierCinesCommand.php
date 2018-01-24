<?php

namespace Application\Command;

use Application\Command\Exception\CommandExecutionException;
use Application\Entity\Db\Fichier;
use Application\Validator\Exception\CinesErrorException;
use DOMDocument;
use UnicaenApp\Exception\RuntimeException;

class ValidationFichierCinesCommand
{
    const XML_TAG_VALIDATOR = 'validator';

    const XML_TAG_VALID = 'valid';
    const XML_TAG_WELLFORMED = 'wellFormed';
    const XML_TAG_ARCHIVABLE = 'archivable';
    const XML_TAG_MESSAGE = 'message';
    const XML_TAG_SIZE = 'size';
    const XML_TAG_MD5SUM = 'md5sum';
    const XML_TAG_SHA256SUM = 'sha256sum';
    const XML_TAG_FORMAT = 'format';
    const XML_TAG_VERSION = 'version';

    /**
     * @var string XML retourné par le web service.
     */
    protected $xml;

    /**
     * @var bool
     */
    protected $simulateError = false;

    /**
     * ValidationFichierCinesCommand constructor.
     *
     * @param string $scriptPath Chemin absolu du script à exécuter.
     */
    public function __construct($scriptPath = null)
    {
        $this->scriptPath = $scriptPath;
    }

    /**
     * @var string Chemin absolu du script à exécuter.
     */
    protected $scriptPath;

    /**
     * @param string $scriptPath
     * @return $this
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;

        return $this;
    }

    /**
     * @param Fichier|string $fichier
     * @param string         $url URL du web service, si différente de celle par défaut
     * @param int            $maxExecutionTime
     * @return bool
     */
    public function execute($fichier, $url = null, $maxExecutionTime = null)
    {
        if ($fichier instanceof Fichier) {
            // création du fichier temporaire sur le disque à partir de la bdd
            $filePath = $fichier->writeFichierToDisk();
        }
        else {
            $filePath = $fichier;
        }

        $this->execValidationRequest($filePath, $url, $maxExecutionTime);

        if ($fichier instanceof Fichier) {
            // suppression du fichier temporaire sur le disque
            unlink($filePath);
        }
    }

    /**
     * @return array
     */
    public function getArrayResult()
    {
        if (! trim($this->xml)) {
            return [];
        }

        // suppression des sauts de ligne sinon loadXML() démissionne au premier rencontré
        $xml = str_replace(PHP_EOL, '', $this->xml);

        try {
            $dom = $this->loadXML($xml);
        } catch (\DOMException $e) {
            throw new RuntimeException("Erreur rencontrée lors du chargement du XML suivant: " . $this->xml, null, $e);
        }

        return [
            $name = self::XML_TAG_WELLFORMED => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_VALID      => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_ARCHIVABLE => $this->extractBooleanFromDom($name, $dom),
            $name = self::XML_TAG_MESSAGE    => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_MD5SUM     => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_SHA256SUM  => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_SIZE       => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_FORMAT     => $this->extractStringFromDom($name, $dom),
            $name = self::XML_TAG_VERSION    => $this->extractStringFromDom($name, $dom),
        ];
    }

    /**
     * @param $xml
     * @return DOMDocument
     */
    private function loadXML($xml)
    {
        set_error_handler(function($errno, $errstr/*, $errfile, $errline*/) {
            if ($errno === E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()") > 0)) {
                throw new \DOMException($errstr);
            }
            return false;
        });

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        restore_error_handler();

        return $dom;
    }

    /**
     * @return bool
     */
    private function detectErrorFromXml()
    {
        if ($this->simulateError) {
            $this->xml = $this->getFakeErrorResponse();
        }

        // suppression des sauts de ligne sinon loadXML() démissionne au premier rencontré
        $xml = str_replace(PHP_EOL, '', $this->xml);

        try {
            $dom = $this->loadXML($xml);
        } catch (\DOMException $e) {
            throw new RuntimeException("Erreur rencontrée lors du chargement du XML suivant: " . $this->xml, null, $e);
        }

        if ($dom->getElementsByTagName(self::XML_TAG_VALIDATOR)->length === 0) {
            return true;
        }

        return false;
    }

    private function extractBooleanFromDom($name, DOMDocument $dom)
    {
        $value = $dom->getElementsByTagName($name)->item(0)->nodeValue;

        return mb_strtolower($value) === 'true' ? true : false;
    }
    private function extractStringFromDom($name, DOMDocument $dom)
    {
        if (! ($element = $dom->getElementsByTagName($name))) {
            return null;
        }
        if (! ($item = $element->item(0))) {
            return null;
        }

        return $item->nodeValue;
    }

    /**
     * Utilise le script spécifié pour valider le fichier.
     *
     * @param string $filePath         Chemin du fichier à tester
     * @param string $url              URL du web service, si différente de celle par défaut
     * @param int    $maxExecutionTime En secondes
     */
    private function execValidationRequest($filePath, $url = null, $maxExecutionTime = null)
    {
        $scriptPath = $this->scriptPath;

        $command = sprintf('%s --file "%s" %s %s',
            realpath($scriptPath),
            $filePath,
            $url ? sprintf('--url "%s"', $url) : '',
            $maxExecutionTime ? sprintf('--maxtime %d', $maxExecutionTime) : '');

        // exécution de la commande
        exec($command, $output, $returnCode);

        try {
            $this->detectErrorFromCommandExecutionResults($command, $output, $returnCode);
        } catch (CommandExecutionException $cee) {
            throw new RuntimeException(sprintf("La ligne de commande '%s' a échoué. %s", $command, $cee->getMessage()));
        }

        $this->xml = trim(implode(PHP_EOL, $output));

        if (! $this->xml) {
            throw new CinesErrorException(
                "Impossible de valider le fichier car le service de validation n'a retourné aucun résultat.");
        }

        if ($this->detectErrorFromXml()) {
            throw new CinesErrorException(
                "Impossible de valider le fichier car le service de validation a semble-t-il rencontré un problème.");
        }
    }

    /**
     * @param string $command
     * @param array  $output
     * @param int    $returnCode
     */
    private function detectErrorFromCommandExecutionResults($command, $output, $returnCode)
    {
        if ($returnCode !== 0) {
            if ($returnCode === CURLE_OPERATION_TIMEOUTED) {
                // curl: (28) Operation timed out after 5001 milliseconds with 0 bytes received
                throw CommandExecutionException::operationTimedout();
            }
            if ($returnCode === CURLE_GOT_NOTHING) {
                throw CommandExecutionException::gotNothing();
            }
            throw CommandExecutionException::unknown();
        }

        if (!is_array($output) || !$output) {
            throw CommandExecutionException::emptyResult();
        }
    }

    /**
     * Retourne le résultat de la validation.
     *
     * @return string
     */
    public function getResult()
    {
        return $this->xml;
    }


    private function getFakeErrorResponse()
    {
        return <<<EOS
<html><head><title>Apache Tomcat/7.0.54 - Rapport d''erreur</title><style><!--H1 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:22px;} H2 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:16px;} H3 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:14px;} BODY {font-family:Tahoma,Arial,sans-serif;color:black;background-color:white;} B {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;} P {font-family:Tahoma,Arial,sans-serif;background:white;color:black;font-size:12px;}A {color : black;}A.name {color : black;}HR {color : #525D76;}--></style> </head><body><h1>Etat HTTP 500 - org.glassfish.jersey.server.ContainerException: java.io.IOException: le fichier  /opt/facile/tmp/sodoct-582061fb0abcd-2016-BROUARD-SALA-QUENTIN-VA.pdf.pdf n'est pas accessible en lecture</h1><HR size="1" noshade="noshade"><p><b>type</b> Rapport d''exception</p><p><b>message</b> <u>org.glassfish.jersey.server.ContainerException: java.io.IOException: le fichier  /opt/facile/tmp/sodoct-582061fb0abcd-2016-BROUARD-SALA-QUENTIN-VA.pdf.pdf n'est pas accessible en lecture</u></p><p><b>description</b> <u>Le serveur a rencontré une erreur interne qui l''a empêché de satisfaire la requête.</u></p><p><b>exception</b> <pre>javax.servlet.ServletException: org.glassfish.jersey.server.ContainerException: java.io.IOException: le fichier  /opt/facile/tmp/sodoct-582061fb0abcd-2016-BROUARD-SALA-QUENTIN-VA.pdf.pdf n'est pas accessible en lecture
	org.glassfish.jersey.servlet.WebComponent.service(WebComponent.java:423)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:386)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:334)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:221)
	org.apache.tomcat.websocket.server.WsFilter.doFilter(WsFilter.java:52)
</pre></p><p><b>cause mère</b> <pre>org.glassfish.jersey.server.ContainerException: java.io.IOException: le fichier  /opt/facile/tmp/sodoct-582061fb0abcd-2016-BROUARD-SALA-QUENTIN-VA.pdf.pdf n'est pas accessible en lecture
	org.glassfish.jersey.servlet.internal.ResponseWriter.rethrow(ResponseWriter.java:256)
	org.glassfish.jersey.servlet.internal.ResponseWriter.failure(ResponseWriter.java:238)
	org.glassfish.jersey.server.ServerRuntime$Responder.process(ServerRuntime.java:486)
	org.glassfish.jersey.server.ServerRuntime$2.run(ServerRuntime.java:316)
	org.glassfish.jersey.internal.Errors$1.call(Errors.java:271)
	org.glassfish.jersey.internal.Errors$1.call(Errors.java:267)
	org.glassfish.jersey.internal.Errors.process(Errors.java:315)
	org.glassfish.jersey.internal.Errors.process(Errors.java:297)
	org.glassfish.jersey.internal.Errors.process(Errors.java:267)
	org.glassfish.jersey.process.internal.RequestScope.runInScope(RequestScope.java:317)
	org.glassfish.jersey.server.ServerRuntime.process(ServerRuntime.java:291)
	org.glassfish.jersey.server.ApplicationHandler.handle(ApplicationHandler.java:1140)
	org.glassfish.jersey.servlet.WebComponent.service(WebComponent.java:403)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:386)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:334)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:221)
	org.apache.tomcat.websocket.server.WsFilter.doFilter(WsFilter.java:52)
</pre></p><p><b>cause mère</b> <pre>java.io.IOException: le fichier  /opt/facile/tmp/sodoct-582061fb0abcd-2016-BROUARD-SALA-QUENTIN-VA.pdf.pdf n'est pas accessible en lecture
	fr.cines.format.validator.ValidatorBean.setFile(ValidatorBean.java:518)
	fr.cines.facile.ws.FacileWebService.validate(FacileWebService.java:150)
	sun.reflect.GeneratedMethodAccessor226.invoke(Unknown Source)
	sun.reflect.DelegatingMethodAccessorImpl.invoke(DelegatingMethodAccessorImpl.java:43)
	java.lang.reflect.Method.invoke(Method.java:498)
	org.glassfish.jersey.server.model.internal.ResourceMethodInvocationHandlerFactory$1.invoke(ResourceMethodInvocationHandlerFactory.java:81)
	org.glassfish.jersey.server.model.internal.AbstractJavaResourceMethodDispatcher$1.run(AbstractJavaResourceMethodDispatcher.java:144)
	org.glassfish.jersey.server.model.internal.AbstractJavaResourceMethodDispatcher.invoke(AbstractJavaResourceMethodDispatcher.java:161)
	org.glassfish.jersey.server.model.internal.JavaResourceMethodDispatcherProvider$ResponseOutInvoker.doDispatch(JavaResourceMethodDispatcherProvider.java:160)
	org.glassfish.jersey.server.model.internal.AbstractJavaResourceMethodDispatcher.dispatch(AbstractJavaResourceMethodDispatcher.java:99)
	org.glassfish.jersey.server.model.ResourceMethodInvoker.invoke(ResourceMethodInvoker.java:389)
	org.glassfish.jersey.server.model.ResourceMethodInvoker.apply(ResourceMethodInvoker.java:347)
	org.glassfish.jersey.server.model.ResourceMethodInvoker.apply(ResourceMethodInvoker.java:102)
	org.glassfish.jersey.server.ServerRuntime$2.run(ServerRuntime.java:308)
	org.glassfish.jersey.internal.Errors$1.call(Errors.java:271)
	org.glassfish.jersey.internal.Errors$1.call(Errors.java:267)
	org.glassfish.jersey.internal.Errors.process(Errors.java:315)
	org.glassfish.jersey.internal.Errors.process(Errors.java:297)
	org.glassfish.jersey.internal.Errors.process(Errors.java:267)
	org.glassfish.jersey.process.internal.RequestScope.runInScope(RequestScope.java:317)
	org.glassfish.jersey.server.ServerRuntime.process(ServerRuntime.java:291)
	org.glassfish.jersey.server.ApplicationHandler.handle(ApplicationHandler.java:1140)
	org.glassfish.jersey.servlet.WebComponent.service(WebComponent.java:403)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:386)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:334)
	org.glassfish.jersey.servlet.ServletContainer.service(ServletContainer.java:221)
	org.apache.tomcat.websocket.server.WsFilter.doFilter(WsFilter.java:52)
</pre></p><p><b>note</b> <u>La trace complète de la cause mère de cette erreur est disponible dans les fichiers journaux de Apache Tomcat/7.0.54.</u></p><HR size="1" noshade="noshade"><h3>Apache Tomcat/7.0.54</h3></body></html>
EOS;

//        $this->xml = <<<EOS
//<html>
//    <head><title>503 Unavailable</title></head>
//    <body>
//        <h1>Erreur</h1>
//    </body>
//</html>
//EOS;
    }
}