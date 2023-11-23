<?php

namespace Formation\Entity\Db;

use Structure\Entity\Db\StructureAwareTrait;
use UnicaenApp\Entity\HistoriqueAwareInterface;
use UnicaenApp\Entity\HistoriqueAwareTrait;

class SessionStructureValide implements HistoriqueAwareInterface
{
    use HistoriqueAwareTrait;
    use StructureAwareTrait;

    private int $id;
    private ?Session $session = null;
    private ?string $lieu = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Session|null
     */
    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * @param Session|null $session
     */
    public function setSession(?Session $session): void
    {
        $this->session = $session;
    }

    /**
     * @return string|null
     */
    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    /**
     * @param string|null $lieu
     */
    public function setLieu(?string $lieu): void
    {
        $this->lieu = $lieu;
    }

}