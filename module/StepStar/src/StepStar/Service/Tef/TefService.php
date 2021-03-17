<?php

namespace StepStar\Service\Tef;

use Application\Service\These\TheseServiceAwareTrait;
use Doctrine\ORM\Query;
use StepStar\Exception\TefServiceException;
use StepStar\Service\Xml\XmlServiceAwareTrait;
use StepStar\Service\Xslt\XsltServiceAwareTrait;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use UnicaenApp\Exception\RuntimeException;

class TefService
{
    use TheseServiceAwareTrait;
    use XmlServiceAwareTrait;
    use XsltServiceAwareTrait;

    /**
     * @var string
     */
    protected $xslFileTemplatePath;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * TefService constructor.
     */
    public function __construct()
    {
        $this->outputDir = sys_get_temp_dir();
    }

    /**
     * @param string $xslFileTemplatePath
     * @return self
     */
    public function setXslTemplatePath(string $xslFileTemplatePath): self
    {
        $this->xslFileTemplatePath = $xslFileTemplatePath;
        return $this;
    }

    /**
     * @param string $outputDir
     * @return TefService
     */
    public function setOutputDir(string $outputDir): TefService
    {
        $this->outputDir = $outputDir;
        return $this;
    }

    /**
     * Exporte dans un fichier XML une seule thèse (spécifiée par son id).
     *
     * Le fichier XML ainsi généré pourra servir d'entrée pour la transformation XSLT en fichiers TEF,
     * cf. {@see generateTefFilesFromXml()}.
     *
     * @param int $theseId
     * @param string $xmlFilePath
     * @throws TefServiceException
     */
    public function exportTheseToXml(int $theseId, string $xmlFilePath)
    {
        if (file_exists($xmlFilePath)) {
            throw new TefServiceException("Le fichier destination spécifié existe déjà : " . $xmlFilePath);
        }

        $arrayHydratedThese = $this->fetchThesesAsArrays([$theseId]);
        $thesesXmlContent = $this->xmlService->generateXmlContentForTheses($arrayHydratedThese);
        file_put_contents($xmlFilePath, $thesesXmlContent);
    }

    /**
     * Exporte dans un fichier XML un ensemble de thèses (chacune spécifiée par son id).
     * Le répertoire destination doit être spécifié via {@see setOutputDir()}.
     *
     * Le fichier XML ainsi généré pourra servir d'entrée pour la transformation XSLT en fichiers TEF,
     * cf. {@see generateTefFilesFromXml()}.
     *
     * @param int[] $thesesIds
     * @return string
     * @throws TefServiceException
     */
    public function exportThesesToXml(array $thesesIds): string
    {
        if ($this->outputDir === null) {
            throw new TefServiceException("Aucun répertoire destination n'a été spécifié");
        }
        if (!file_exists($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }

        $arrayHydratedTheses = $this->fetchThesesAsArrays($thesesIds);

        $thesesXmlContent = $this->xmlService->generateXmlContentForTheses($arrayHydratedTheses);
        $xmlFilePath = $this->outputDir . '/' . uniqid('stepstar_theses_') . '.xml';
        file_put_contents($xmlFilePath, $thesesXmlContent);

        return $xmlFilePath;
    }

    /**
     * Transforme via XSLT le fichier XML spécifié contenant les thèses pour produire les fichiers TEF.
     *
     * @param string $xmlFilePath
     * @throws TefServiceException
     */
    public function generateTefFilesFromXml(string $xmlFilePath)
    {
        if ($this->outputDir === null) {
            throw new TefServiceException("Aucun répertoire destination n'a été spécifié");
        }
        if (file_exists($this->outputDir)) {
            throw new TefServiceException("Le répertoire destination spécifié existe déjà : " . $this->outputDir);
        }
        mkdir($this->outputDir, 0777, true);

        // .xsl
        $xslFileContent = $this->generateXslFileContent();
        $xslFilePath = $this->outputDir . '/' . uniqid('stepstar_') . '.xsl';
        file_put_contents($xslFilePath, $xslFileContent);

        // transformation XSLT
        $this->xsltService->setOutputDir($this->outputDir);
        $this->xsltService->transformToFiles($xmlFilePath, $xslFilePath);
    }

    /**
     * Génération du fichier XSL à partir du template Twig.
     *
     * @return string
     * @throws TefServiceException
     */
    private function generateXslFileContent(): string
    {
        $templateFile = $this->xslFileTemplatePath;
        if (!file_exists($templateFile)) {
            throw new TefServiceException("Template $templateFile introuvable.");
        }

        $templatesDir = dirname($templateFile);
        $loader = new FilesystemLoader($templatesDir);
        $twig = new Environment($loader, [
//            'cache' => '/path/to/compilation_cache',
        ]);

        $params = [
            'etablissement' => 'NORM',
            'autoriteSudoc_etabSoutenance' => '190906332',
            'thesesRootTag' => 'THESES',
            'theseTag' => 'THESE',
        ];
        $templateFileName = basename($templateFile);
        try {
            return $twig->render($templateFileName, $params);
        } catch (Error $e) {
            throw new TefServiceException("Erreur Twig rencontrée", null, $e);
        }
    }


    /**
     * Recherche et hydrate au format array les thèses spécifiées, avec toutes les jointures requises pour
     * exporter les thèses au format XML.
     *
     * @param int[] $thesesIds Ids des thèses concernées
     * @return array[] Thèses trouvées, hydrtaées au format array
     */
    private function fetchThesesAsArrays(array $thesesIds): array
    {
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->addSelect('e, d, di, ed, ur, es, eds, urs, a, ai, r')
            ->join('t.etablissement', 'e')
            ->join('t.doctorant', 'd')
            ->join('d.individu', 'di')
            ->join('t.ecoleDoctorale', 'ed')
            ->join('t.uniteRecherche', 'ur')
            ->join('e.structure', 'es')
            ->join('ed.structure', 'eds')
            ->join('ur.structure', 'urs')
            ->leftJoin('t.acteurs', 'a')
            ->leftJoin('a.individu', 'ai')
            ->leftJoin('a.role', 'r')
            ->where($qb->expr()->in('t.id', $thesesIds));

        $theses = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

        if (count($theses) !== count($thesesIds)) {
            throw new RuntimeException("Certaines thèses spécifiées sont introuvables.");
        }

        return $theses;
    }
}