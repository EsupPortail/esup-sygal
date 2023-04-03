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
 * Ce bouton est associé à plusieurs champs sources possibles, desquels est prélevé le texte à rechercher (PPN, nom de personne, etc.)
 * et à un champ texte destination dans lequel sera inscrit l'identifiant PPN de la notice sélectionnée.
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

    private array $sourceElements;
    private string $destinationElement;

    public function __construct()
    {
        $this->button = new Button('button');
        $this->button->setLabel('');

        $this->params = new Params();
        $this->params->setIndex1((new Index1)->setNomDePersonne('')); // todo : encore utile ?
    }

    /**
     * Point d'entrée.
     *
     * @param array $sourceElements Éléments sources possibles à partir desquels seront prélevés le texte à rechercher dans l'interface web d'IdRef.
     * Exemple de valeur :
     *      <pre>
     *      [
     *         ['Index1' => Index1::INDEX_Ppn, 'Index1Value' => ['#idRef']],
     *         ['Index1' => Index1::INDEX_NomDePersonne, 'Index1Value' => ['#nomUsuel', '#prenom1']],
     *      ]
     *      </pre>
     * Signification :
     *      Si l'élément HTML '#idRef' est renseigné, alors la recherche visera un "Identifiant IdRef (n°PPN)" (Index1::INDEX_Ppn) avec
     *      comme texte recherché la valeur de cet élément.
     *      Sinon, la recherche visera un "Nom de personne" (Index1::INDEX_NomDePersonne) avec comme texte recherché la concaténation
     *      des valeurs éventuelles des éléments HTML '#nomUsuel' et '#prenom1'.
     *
     * @param string $destinationElement Élément destination dans lequel sera inscrit le PPN de la notice sélectionnée.
     * Exemple : '#idRef'.
     */
    public function __invoke(array $sourceElements, string $destinationElement): self
    {
        $this->sourceElements = $sourceElements;
        $this->destinationElement = $destinationElement;

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
            ->setAttribute('data-source-elements', json_encode($this->sourceElements))
            ->setAttribute('data-destination-element', $this->destinationElement);

        return $this->view->partial($this->partial, [
            'button' => $this->button,
            'params' => $this->params,
        ]);
    }
}