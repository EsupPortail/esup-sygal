<?php

namespace Formation\Entity\Db\Traits;

use Formation\Entity\Db\Interfaces\HasModaliteInterface;

trait HasModaliteTrait {

    private ?string $modalite = null;

    /**
     * @return string|null
     */
    public function getModalite(): ?string
    {
        return $this->modalite;
    }

    /**
     * @param string|null $modalite
     * @return HasModaliteInterface
     */
    public function setModalite(?string $modalite): HasModaliteInterface
    {
        $this->modalite = $modalite;
        return $this;
    }
}