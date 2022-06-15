<?php

namespace UnicaenAvis\Entity\Db;

use InvalidArgumentException;

/**
 * AvisComplem
 */
class AvisComplem
{
    /**
     * @var string|null
     */
    private ?string $valeur;

    /**
     * @var int
     */
    private int $id;

    /**
     * @var \UnicaenAvis\Entity\Db\Avis
     */
    private Avis $avis;

    /**
     * @var \UnicaenAvis\Entity\Db\AvisTypeValeurComplem
     */
    private AvisTypeValeurComplem $avisTypeValeurComplem;


    /**
     * Méthode de tri selon l'attribut 'ordre' du type de complément.
     *
     * @param \UnicaenAvis\Entity\Db\AvisComplem $ac1
     * @param \UnicaenAvis\Entity\Db\AvisComplem $ac2
     * @return int
     */
    static public function sorterByOrdre(AvisComplem $ac1, AvisComplem $ac2): int
    {
        return $ac1->getAvisTypeValeurComplem()->getOrdre() <=> $ac2->getAvisTypeValeurComplem()->getOrdre();
    }

    /**
     * Set valeurComplement.
     *
     * @param string|null $valeur
     *
     * @return AvisComplem
     */
    public function setValeur($valeur = null)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * @return string
     */
    public function getValeurToHtml(): string
    {
        if ($this->valeur === null) {
            return '';
        }

        switch ($this->getAvisTypeValeurComplem()->getType()) {
            case AvisTypeValeurComplem::TYPE_COMPLEMENT_CHECKBOX:
                if ($this->valeur === '1') {
                    return sprintf(
                        '- <span class="avis-complem-checkbox"><span class="avis-complem-libelle"></span><span class="avis-complem-valeur">%s</span></span>',
                        $this->getAvisTypeValeurComplem()->getLibelle()
                    );
                } else {
                    return '';
                }
            case AvisTypeValeurComplem::TYPE_COMPLEMENT_TEXTAREA:
                return sprintf(
                    '%s<span class="avis-complem-textarea"><span class="avis-complem-libelle">%s</span> : <span class="avis-complem-valeur">%s</span></span>',
                    $this->getAvisTypeValeurComplem()->getAvisTypeValeurComplemParent() ? '' : '- ',
                    rtrim($this->getAvisTypeValeurComplem()->getLibelle(), ' :'),
                    preg_replace("/\r\n|\n|\r/", '<br>', $this->valeur)
                );
            default:
                throw new InvalidArgumentException("Type inattendu !");
        }
    }

    /**
     * Get valeurComplement.
     *
     * @return string|null
     */
    public function getValeur()
    {
        return $this->valeur;
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
     * Set avis.
     *
     * @param \UnicaenAvis\Entity\Db\Avis|null $avis
     *
     * @return AvisComplem
     */
    public function setAvis(\UnicaenAvis\Entity\Db\Avis $avis = null)
    {
        $this->avis = $avis;

        return $this;
    }

    /**
     * Get avis.
     *
     * @return \UnicaenAvis\Entity\Db\Avis|null
     */
    public function getAvis()
    {
        return $this->avis;
    }

    /**
     * Set avisTypeValeurComplem.
     *
     * @param \UnicaenAvis\Entity\Db\AvisTypeValeurComplem|null $avisTypeValeurComplem
     *
     * @return AvisComplem
     */
    public function setAvisTypeValeurComplem(AvisTypeValeurComplem $avisTypeValeurComplem = null)
    {
        $this->avisTypeValeurComplem = $avisTypeValeurComplem;

        return $this;
    }

    /**
     * Get avisTypeValeurComplem.
     *
     * @return \UnicaenAvis\Entity\Db\AvisTypeValeurComplem|null
     */
    public function getAvisTypeValeurComplem(): ?AvisTypeValeurComplem
    {
        return $this->avisTypeValeurComplem;
    }
}
