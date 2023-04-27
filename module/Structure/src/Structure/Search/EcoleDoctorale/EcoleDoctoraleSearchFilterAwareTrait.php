<?php

namespace Structure\Search\EcoleDoctorale;

trait EcoleDoctoraleSearchFilterAwareTrait
{
    /**
     * @var EcoleDoctoraleSearchFilter
     */
    protected $ecoleDoctoraleSearchFilter;

    /**
     * @return EcoleDoctoraleSearchFilter
     */
    public function getEcoleDoctoraleSearchFilter(): EcoleDoctoraleSearchFilter
    {
        if ($this->ecoleDoctoraleSearchFilter === null) {
            $this->ecoleDoctoraleSearchFilter = EcoleDoctoraleSearchFilter::newInstance();
        }
        return $this->ecoleDoctoraleSearchFilter;
    }

    /**
     * @param EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter
     */
    public function setEcoleDoctoraleSearchFilter(EcoleDoctoraleSearchFilter $ecoleDoctoraleSearchFilter)
    {
        $this->ecoleDoctoraleSearchFilter = $ecoleDoctoraleSearchFilter;
    }
}