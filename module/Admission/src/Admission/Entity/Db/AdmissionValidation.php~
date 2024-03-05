<?php
namespace Admission\Entity\Db;

use Application\Constants;
use Individu\Entity\Db\Individu;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class AdmissionValidation implements HistoriqueAwareInterface, AdmissionOperationInterface, ResourceInterface {

    use HistoriqueAwareTrait;

    private ?int $id = null;
    
    private ?TypeValidation $typeValidation = null;

    private ?Individu $individu;

    private ?Admission $admission = null;

    public function __construct(?TypeValidation $type = null, ?Admission $admission = null, ?Individu $individu = null)
    {
        if ($type !== null) {
            $this->setTypeValidation($type);
        }
        if ($admission !== null) {
            $this->setAdmission($admission);
        }
        if ($individu !== null) {
            $this->setIndividu($individu);
        }
    }

    public function __toString(): string
    {
        $str = (string) $this->getTypeValidation();

//        if ($date = $this->getHistoModification() ?: $this->getHistoCreation()) {
//            $str .= sprintf(" (le %s par %s)",
//                $date->format(Constants::DATETIME_FORMAT),
//                $this->getHistoModificateur() ?: $this->getHistoCreateur());
//        }

        return $str;
    }

    public function matches(AdmissionOperationInterface $otherOperation): bool
    {
        return
            $otherOperation instanceof self && (
                // même id non null ou même type de validation
                $this->getId() && $otherOperation->getId() && $this->getId() === $otherOperation->getId() ||
                $this->getTypeValidation() === $otherOperation->getTypeValidation()
            );
    }

    public function getTypeToString(): string
    {
        return (string) $this->getTypeValidation();
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
     * Set typeValidation.
     *
     * @param TypeValidation|null $typeValidation
     *
     * @return AdmissionValidation
     */
    public function setTypeValidation(TypeValidation $typeValidation = null)
    {
        $this->typeValidation = $typeValidation;

        return $this;
    }

    /**
     * Get typeValidation.
     *
     * @return TypeValidation|null
     */
    public function getTypeValidation()
    {
        return $this->typeValidation;
    }

    /**
     * Set individuId.
     *
     * @param Individu|null $individu
     *
     * @return AdmissionValidation
     */
    public function setIndividu(Individu $individu = null)
    {
        $this->individu = $individu;

        return $this;
    }

    /**
     * Get individuId.
     *
     * @return Individu|null
     */
    public function getIndividu()
    {
        return $this->individu;
    }

    public function setAdmission(Admission $admission = null) : self
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * Get admission.
     *
     * @return Admission
     */
    public function getAdmission(): Admission
    {
        return $this->admission;
    }

    public function getValeurBool(): bool
    {
        return true;
    }

    public function getResourceId()
    {
        return "AdmissionValidation";
    }

    /** Fonction pour les macros du module UnicaenRenderer ************************************************************/

    /**
     * @noinspection
     * @return string
     */
    public function getAuteurToString() : string
    {
        $displayname = $this->getIndividu()->getNomComplet(false, false, false, true);
        return $displayname;
    }
    /**
     * @noinspection
     * @return string
     */
    public function getDateToString() : string
    {
        $date = $this->getHistoCreation()->format('d/m/Y à H:i');
        return $date;
    }

    /**
     * @noinspection
     * @return string
     */
    public function getDestructeurToString() : string
    {
        return "par ".$this->getHistoDestructeur();
    }
}
