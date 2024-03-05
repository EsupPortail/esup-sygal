<?php
namespace Admission\Entity\Db;

use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class ConventionFormationDoctorale implements HistoriqueAwareInterface, ResourceInterface {

    use HistoriqueAwareTrait;


    /**
     * @var string|null
     */
    private $calendrierProjetRecherche;

    /**
     * @var string|null
     */
    private $modalitesEncadrSuiviAvancmtRech;

    /**
     * @var string|null
     */
    private $conditionsRealisationProjRech;

    /**
     * @var string|null
     */
    private $modalitesIntegrationUr;

    /**
     * @var string|null
     */
    private $partenariatsProjThese;

    /**
     * @var string|null
     */
    private $motivationDemandeConfidentialite;

    /**
     * @var string|null
     */
    private $projetProDoctorant;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    public function __toString()
    {
        return strval( $this->getId() );
    }


    /**
     * Set calendrierProjetRecherche.
     *
     * @param string|null $calendrierProjetRecherche
     *
     * @return ConventionFormationDoctorale
     */
    public function setCalendrierProjetRecherche($calendrierProjetRecherche = null)
    {
        $this->calendrierProjetRecherche = $calendrierProjetRecherche;

        return $this;
    }

    /**
     * Get calendrierProjetRecherche.
     *
     * @return string|null
     */
    public function getCalendrierProjetRecherche()
    {
        return $this->calendrierProjetRecherche;
    }

    /**
     * Set modalitesEncadrSuiviAvancmtRech.
     *
     * @param string|null $modalitesEncadrSuiviAvancmtRech
     *
     * @return ConventionFormationDoctorale
     */
    public function setModalitesEncadrSuiviAvancmtRech($modalitesEncadrSuiviAvancmtRech = null)
    {
        $this->modalitesEncadrSuiviAvancmtRech = $modalitesEncadrSuiviAvancmtRech;

        return $this;
    }

    /**
     * Get modalitesEncadrSuiviAvancmtRech.
     *
     * @return string|null
     */
    public function getModalitesEncadrSuiviAvancmtRech()
    {
        return $this->modalitesEncadrSuiviAvancmtRech;
    }

    /**
     * Set conditionsRealisationProjRech.
     *
     * @param string|null $conditionsRealisationProjRech
     *
     * @return ConventionFormationDoctorale
     */
    public function setConditionsRealisationProjRech($conditionsRealisationProjRech = null)
    {
        $this->conditionsRealisationProjRech = $conditionsRealisationProjRech;

        return $this;
    }

    /**
     * Get conditionsRealisationProjRech.
     *
     * @return string|null
     */
    public function getConditionsRealisationProjRech()
    {
        return $this->conditionsRealisationProjRech;
    }

    /**
     * Set modalitesIntegrationUr.
     *
     * @param string|null $modalitesIntegrationUr
     *
     * @return ConventionFormationDoctorale
     */
    public function setModalitesIntegrationUr($modalitesIntegrationUr = null)
    {
        $this->modalitesIntegrationUr = $modalitesIntegrationUr;

        return $this;
    }

    /**
     * Get modalitesIntegrationUr.
     *
     * @return string|null
     */
    public function getModalitesIntegrationUr()
    {
        return $this->modalitesIntegrationUr;
    }

    /**
     * Set partenariatsProjThese.
     *
     * @param string|null $partenariatsProjThese
     *
     * @return ConventionFormationDoctorale
     */
    public function setPartenariatsProjThese($partenariatsProjThese = null)
    {
        $this->partenariatsProjThese = $partenariatsProjThese;

        return $this;
    }

    /**
     * Get partenariatsProjThese.
     *
     * @return string|null
     */
    public function getPartenariatsProjThese()
    {
        return $this->partenariatsProjThese;
    }

    /**
     * Set motivationDemandeConfidentialite.
     *
     * @param string|null $motivationDemandeConfidentialite
     *
     * @return ConventionFormationDoctorale
     */
    public function setMotivationDemandeConfidentialite($motivationDemandeConfidentialite = null)
    {
        $this->motivationDemandeConfidentialite = $motivationDemandeConfidentialite;

        return $this;
    }

    /**
     * Get motivationDemandeConfidentialite.
     *
     * @return string|null
     */
    public function getMotivationDemandeConfidentialite()
    {
        return $this->motivationDemandeConfidentialite;
    }

    /**
     * Set projetProDoctorant.
     *
     * @param string|null $projetProDoctorant
     *
     * @return ConventionFormationDoctorale
     */
    public function setProjetProDoctorant($projetProDoctorant = null)
    {
        $this->projetProDoctorant = $projetProDoctorant;

        return $this;
    }

    /**
     * Get projetProDoctorant.
     *
     * @return string|null
     */
    public function getProjetProDoctorant()
    {
        return $this->projetProDoctorant;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set admission.
     *
     * @param Admission|null $admission
     *
     * @return ConventionFormationDoctorale
     */
    public function setAdmission(Admission $admission = null)
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return Admission|null
     */
    public function getAdmission()
    {
        return $this->admission;
    }

    public function getResourceId()
    {
        return "ConventionFormationDoctorale";
    }
}
