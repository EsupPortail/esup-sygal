<?php

namespace StepStar\Facade;

use Laminas\Stdlib\Glob;

/**
 * Trait regroupant des paramètres concernant le nommage des fichiers TEF générés / pris en compte par le module,
 * **et ayant une cohérence entre eux**.
 */
trait TefFileNameParamsAwareTrait
{
    /**
     * Expression utilisée pour fabriquer le nom du fichier TEF généré par le module pour une thèse.
     * **ATTENTION, la partie extension doit exprimer "la même chose" que les autres propriétés.**
     * @var string
     */
    protected string $tefResultDocumentHref = '{$ETABLISSEMENT}_{THESE_ID}_{CODE_ETAB_SOUT}_{CODE_ETUDIANT}.tef.xml';

    /**
     * Exemple de nom de fichier TEF fabriqué (et donc exploitable) par le module pour une thèse.
     * @var string
     */
    protected string $tefResultDocumentHrefExample = "NORM_12345_0761904G_1234567.tef.xml" .
        " où " .
        "'NORM' est l'identifiant STEP/STAR de l'établissement, " .
        "'12345' l'identifiant unique SyGAL de la thèse, " .
        "'0761904G' l'identifiant RNE/UAI de l'établissement, " .
        "'1234567' un identifiant quelconque de l'étudiant.";

    /**
     * Expression régulière permettant d'extraire du nom de fichier TEF l'id de la thèse.
     * **ATTENTION, elle doit être cohérente avec la propriété {@see tefResultDocumentHref}.**
     * **ATTENTION, la partie extension doit exprimer "la même chose" que les autres propriétés.**
     * @var string
     */
    protected string $tefResultDocumentHrefTheseIdPregMatchPattern = '/^.+_(.+)_.+_.+\.tef\.xml$/U';

    /**
     * Motif permettant avec {@see Glob::glob()} de lister les fichiers TEF présents dans un répertoire.
     * **ATTENTION, la partie extension doit exprimer "la même chose" que les autres propriétés.**
     * @var string
     */
    protected string $listTefFilesInDirectoryGlobPattern = '*.tef.xml';
}