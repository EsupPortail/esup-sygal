<?php

namespace Formation\Entity\Db;

/**
 * Inscription
 */
class Inscription
{
    /**
     * @var string|null
     */
    private $liste;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var \DateTime|null
     */
    private $validationEnquete;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $presences;

    /**
     * @var \Formation\Entity\Db\Session
     */
    private $session;

    /**
     * @var \Doctorant\Entity\Db\Doctorant
     */
    private $doctorant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->presences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set liste.
     *
     * @param string|null $liste
     *
     * @return Inscription
     */
    public function setListe($liste = null)
    {
        $this->liste = $liste;

        return $this;
    }

    /**
     * Get liste.
     *
     * @return string|null
     */
    public function getListe()
    {
        return $this->liste;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Inscription
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set validationEnquete.
     *
     * @param \DateTime|null $validationEnquete
     *
     * @return Inscription
     */
    public function setValidationEnquete($validationEnquete = null)
    {
        $this->validationEnquete = $validationEnquete;

        return $this;
    }

    /**
     * Get validationEnquete.
     *
     * @return \DateTime|null
     */
    public function getValidationEnquete()
    {
        return $this->validationEnquete;
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
     * Add presence.
     *
     * @param \Formation\Entity\Db\Presence $presence
     *
     * @return Inscription
     */
    public function addPresence(\Formation\Entity\Db\Presence $presence)
    {
        $this->presences[] = $presence;

        return $this;
    }

    /**
     * Remove presence.
     *
     * @param \Formation\Entity\Db\Presence $presence
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePresence(\Formation\Entity\Db\Presence $presence)
    {
        return $this->presences->removeElement($presence);
    }

    /**
     * Get presences.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPresences()
    {
        return $this->presences;
    }

    /**
     * Set session.
     *
     * @param \Formation\Entity\Db\Session|null $session
     *
     * @return Inscription
     */
    public function setSession(\Formation\Entity\Db\Session $session = null)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session.
     *
     * @return \Formation\Entity\Db\Session|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set doctorant.
     *
     * @param \Doctorant\Entity\Db\Doctorant|null $doctorant
     *
     * @return Inscription
     */
    public function setDoctorant(\Doctorant\Entity\Db\Doctorant $doctorant = null)
    {
        $this->doctorant = $doctorant;

        return $this;
    }

    /**
     * Get doctorant.
     *
     * @return \Doctorant\Entity\Db\Doctorant|null
     */
    public function getDoctorant()
    {
        return $this->doctorant;
    }
}
