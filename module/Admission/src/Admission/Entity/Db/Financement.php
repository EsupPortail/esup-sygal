<?php
namespace Admission\Entity\Db;

use Application\Entity\Db\OrigineFinancement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class Financement implements HistoriqueAwareInterface{

    use HistoriqueAwareTrait;

    /**
     * @var bool|null
     */
    private $contratDoctoral;

    /**
     * @var OrigineFinancement
     */
    private $financement;

    /**
     * @var string|null
     */
    private $detailContratDoctoral;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Admission
     */
    private $admission;

    /**
     * @var Collection
     */
    private $verificationFinancement;
    /**
     * @var integer|null
     */
    private $tempsTravail;

    /**
     * @var string|null
     */
    private $statutProfessionnel;

    /**
     * @var bool|null
     */
    private $estSalarie;

    /**
     * @var string|null
     */
    private $etablissementLaboratoireRecherche;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->verificationFinancement = new ArrayCollection();
    }

    /**
     * Set contratDoctoral.
     *
     * @param bool|null $contratDoctoral
     *
     * @return Financement
     */
    public function setContratDoctoral($contratDoctoral = null)
    {
        $this->contratDoctoral = $contratDoctoral;

        return $this;
    }

    /**
     * Get contratDoctoral.
     *
     * @return bool|null
     */
    public function getContratDoctoral()
    {
        return $this->contratDoctoral;
    }

    /**
     * Set financement.
     *
     * @param OrigineFinancement $financement
     *
     * @return Financement|null
     */
    public function setFinancement($financement = null)
    {
        $this->financement = $financement;

        return $this;
    }

    /**
     * Get financement.
     *
     * @return OrigineFinancement|null
     */
    public function getFinancement()
    {
        return $this->financement;
    }

    /**
     * Set detailContratDoctoral.
     *
     * @param string|null $detailContratDoctoral
     *
     * @return Financement
     */
    public function setDetailContratDoctoral($detailContratDoctoral = null)
    {
        $this->detailContratDoctoral = $detailContratDoctoral;

        return $this;
    }

    /**
     * Get detailContratDoctoral.
     *
     * @return string|null
     */
    public function getDetailContratDoctoral()
    {
        return $this->detailContratDoctoral;
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
     * @return Financement
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

    /**
     * Get verificationFinancement.
     *
     * @return Collection
     */
    public function getVerificationFinancement(): Collection
    {
        return $this->verificationFinancement;
    }

    /**
     * Add VerificationFinancement.
     */
    public function addVerificationFinancement(Collection $verificationFinancements)
    {
//        foreach ($verificationFinancements as $vI) {
//            if (!$this->verificationFinancement->contains($vI)) {
//                $this->verificationFinancement->add($vI);
//            }
//        }

        return $this;
    }

    /**
     * Remove VerificationFinancement.
     */
    public function removeVerificationFinancement(Collection $verificationFinancements)
    {
        foreach ($verificationFinancements as $vI) {
            $this->verificationFinancement->removeElement($vI);
        }
    }

    /**
     * Set tempsTravail.
     *
     * @param integer|null $tempsTravail
     *
     * @return Financement
     */
    public function setTempsTravail($tempsTravail = null)
    {
        $this->tempsTravail = $tempsTravail;

        return $this;
    }

    /**
     * Get tempsTravail.
     *
     * @return integer|null
     */
    public function getTempsTravail()
    {
        return $this->tempsTravail;
    }

    /**
     * Set statutProfessionnel.
     *
     * @param string|null $statutProfessionnel
     *
     * @return Financement
     */
    public function setStatutProfessionnel($statutProfessionnel = null)
    {
        $this->statutProfessionnel = $statutProfessionnel;

        return $this;
    }

    /**
     * Get statutProfessionnel.
     *
     * @return string|null
     */
    public function getStatutProfessionnel()
    {
        return $this->statutProfessionnel;
    }

    /**
     * Set estSalarie.
     *
     * @param bool|null $estSalarie
     *
     * @return Financement
     */
    public function setEstSalarie($estSalarie = null)
    {
        $this->estSalarie = $estSalarie;

        return $this;
    }

    /**
     * Get estSalarie.
     *
     * @return bool|null
     */
    public function getEstSalarie()
    {
        return $this->estSalarie;
    }

    /**
     * Set etablissementLaboratoireRecherche.
     *
     * @param string|null $etablissementLaboratoireRecherche
     *
     * @return Financement
     */
    public function setEtablissementLaboratoireRecherche($etablissementLaboratoireRecherche = null)
    {
        $this->etablissementLaboratoireRecherche = $etablissementLaboratoireRecherche;

        return $this;
    }

    /**
     * Get etablissementLaboratoireRecherche.
     *
     * @return string|null
     */
    public function getEtablissementLaboratoireRecherche()
    {
        return $this->etablissementLaboratoireRecherche;
    }

    /** Pour macro ****************************************************************************************************/

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getContratDoctoralLibelle()
    {
        if($this->getContratDoctoral() === null){
            return "<b>Non renseigné</b>";
        }else{
            if($this->getContratDoctoral()){
                return $this->getFinancement() ?
                        $this->getFinancement()->getLibelleLong() :
                        'Aucun employeur choisi';
            }else{
                return "<b>Aucun contrat doctoral prévu</b>";
            }
        }
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getTempsTravailInformations(){
        if($this->tempsTravail == 1){
            return "temps complet";
        }else if($this->tempsTravail == 2){
            return "à temps partiel";
        }else{
            return "<b>Non renseigné</b>";
        }
    }

    /**
     * @noinspection PhpUnusedMethod (il s'agit d'une méthode utilisée par les macros)
     */
    public function getEstSalarieInfos(){
        if($this->estSalarie == 1){
            return "Oui";
        }else if($this->estSalarie == 2){
            return "Non";
        }else{
            return "<b>Non renseigné</b>";
        }
    }
}
