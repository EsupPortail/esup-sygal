<?php

namespace Soutenance\Form\Configuration;

trait ConfigurationFormAwareTrait {

    /** @var ConfigurationForm $configurationForm */
    private $configurationForm;

    /**
     * @return ConfigurationForm
     */
    public function getConfigurationForm()
    {
        return $this->configurationForm;
    }

    /**
     * @param ConfigurationForm $configurationForm
     * @return ConfigurationForm
     */
    public function setConfigurationForm($configurationForm)
    {
        $this->configurationForm = $configurationForm;
        return $this->configurationForm;
    }


}