<?php

namespace UnicaenIdref\View\Helper;

use Laminas\Form\Element\Button;
use Laminas\View\Helper\AbstractHelper;
use UnicaenIdref\Domain\Index1;
use UnicaenIdref\Params;

/**
 * Aide de vue dessinant un bouton affichant l'interface web d'IdRef pour rechercher la notice d'une personne/structure
 * dont on veut "importer" l'identifiant PPN dans un formulaire.
 *
 * Ce bouton est associé a minima à un champ texte source, duquel est prélevé le texte à rechercher (PPN, nom de personne, etc.).
 * Il peut être associé à un 2e champ texte destination, dans lequel sera inscrit l'identifiant PPN de la notice sélectionnée.
 * Si aucun champ texte destination n'est spécifié, l'identifiant PPN de la notice sélectionnée sera inscrit dans le champ texte source.
 *
 * Par défaut, cette aide de vue est paramétrée pour que la recherche se fasse sur l'identifiant PPN.
 *
 * @see https://documentation.abes.fr/aideidrefdeveloppeur/index.html#InterconnecterBaseEtIdref
 *
 * @property \Laminas\View\Renderer\PhpRenderer $view
 */
class IdrefPopupTriggerViewHelper extends AbstractHelper
{
    protected string $partial = 'unicaen-idref/partial/idref-trigger';

    protected Button $button;
    protected Params $params;

    private string $sourceElementId;
    private string $destinationElementId;

    public function __construct()
    {
        $this->button = new Button('button');
        $this->button->setLabel('');

        // par défaut, la recherche se fait sur le PPN
        $this->params = new Params();
        $this->params->setIndex1((new Index1)->setPpn(''));
    }

    /**
     * Point d'entrée.
     *
     * @param string $sourceElementId Id HTML de l'élément source, duquel est prélevé le texte à rechercher
     * @param string|null $destinationElementId Id HTML de l'élément destination dans lequel sera inscrit le PPN de la notice sélectionnée
     */
    public function __invoke(string $sourceElementId, ?string $destinationElementId = null): self
    {
        $this->sourceElementId = $sourceElementId;
        $this->destinationElementId = $destinationElementId ?: $sourceElementId;

        return $this;
    }

    /**
     * Spécifie les paramètres de recherche.
     *
     * @param Params $params Ex pour rechercher selon le PPN : `(new Params())->setIndex1((new Index1)->setPpn('123456789'));`
     * @return self
     */
    public function setParams(Params $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function __toString(): string
    {
        if (!$this->button->getAttribute('id')) {
            $this->button->setAttribute('id', uniqid('idref-popup-trigger-'));
        }
        $this->button
            ->setAttribute('class', 'idref-popup-trigger ' . $this->button->getAttribute('class'))
            ->setAttribute('href', '#')
            ->setAttribute('data-source-element-id', $this->sourceElementId)
            ->setAttribute('data-destination-element-id', $this->destinationElementId);

        return $this->view->partial($this->partial, [
            'button' => $this->button,
            'params' => $this->params,
        ]);
    }
}