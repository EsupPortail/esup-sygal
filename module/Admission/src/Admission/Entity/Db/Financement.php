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
}
