<?php

namespace Import\Model;

/**
 * TmpActeur
 */
class TmpActeur
{
    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $etablissementId;

    /**
     * @var string
     */
    private $individuId;

    /**
     * @var string
     */
    private $theseId;

    /**
     * @var string
     */
    private $roleId;

    /**
     * @var string
     */
    private $libQualite;

    /**
     * @var string
     */
    private $libEtablissement;

    /**
     * @var string
     */
    private $codeQualite;

    /**
     * @var string
     */
    private $codeEtablissement;

    /**
     * @var string
     */
    private $codeRoleJury;

    /**
     * @var string
     */
    private $libRoleJury;

    /**
     * @var string
     */
    private $temoinHDR;

    /**
     * @var string
     */
    private $temoinRapport;

    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getIndividuId()
    {
        return $this->individuId;
    }
}

