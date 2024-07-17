<?php

namespace Admission\Search\Admission;

trait AdmissionTextSearchFilterAwareTrait
{
    /**
     * @var AdmissionTextSearchFilter
     */
    protected $admissionTextSearchFilter;

    /**
     * @return AdmissionTextSearchFilter
     */
    public function getAdmissionTextSearchFilter(): AdmissionTextSearchFilter
    {
        if ($this->admissionTextSearchFilter === null) {
            $this->admissionTextSearchFilter = AdmissionTextSearchFilter::newInstance();
        }
        return $this->admissionTextSearchFilter;
    }

    /**
     * @param AdmissionTextSearchFilter $admissionTextSearchFilter
     */
    public function setAdmissionTextSearchFilter(AdmissionTextSearchFilter $admissionTextSearchFilter)
    {
        $this->admissionTextSearchFilter = $admissionTextSearchFilter;
    }
}