<?php

namespace Application\Filter;

use Application\Entity\Db\Individu;
use Zend\View\Helper\HtmlList;
use Zend\Filter\AbstractFilter;
use Zend\View\Renderer\PhpRenderer;

class IndividuFormatter extends AbstractFilter {

    /** @var bool */
    private $asUl = false;
    /** @var bool */
    private $asSeparated = true;
    /** @var bool */
    private $asArray = true;
    /** @var string */
    private $separator;

    /** Set the returned data type to unordered list
     *  @return $this
     */
    public function asUl()
    {
        $this->asUl = true;
        $this->asSeparated = false;
        $this->asArray = false;

        return $this;
    }

    /** Set the returned data type to separated value format and set the separator
     * @param string $separator (default = ", ")
     * @return $this
     */
    public function asSeparated($separator = ", ")
    {
        $this->asUl = false;
        $this->asSeparated = true;
        $this->separator = $separator;
        $this->asArray = false;

        return $this;
    }

    /** Set the returned data type to separated value format and set the separator
     * @param string $separator (default = ", ")
     * @return $this
     */
    public function asArray($separator = ", ")
    {
        $this->asUl = false;
        $this->asSeparated = false;
        $this->asArray = true;
        $this->separator = $separator;

        return $this;
    }

    /** Format n array of individus
     * @param Individu[] $individus
     * @return string >>> formated set of individus
     */
    public function doFormat($individus)
    {
        if ($this->asUl) {
            $result = $this->doFormatUnorderedList($individus);
        }
        elseif ($this->asSeparated) {
            $result = $this->doFormatSeparated($individus);
        }
        elseif ($this->asArray()) {
            $result = $this->doFormatArray($individus);
        }
        else {
            throw new \LogicException("Cas inattendu !");
        }

        return $result;
    }

    /** This function format an array of acteurs as a unordered list
     * @param Individu[] $individus
     * @return string >>> an unordered list
     */
    private function doFormatUnorderedList($individus) {
        $individus = array_map([$this, 'htmlifyActeur'], $individus);
        $helper = new HtmlList();
        $helper->setView(new PhpRenderer());
        $results = $helper($individus, $ordered = false, $attribs = false, $escape = false);
        return $results;
    }

    /** This function format an array of acteurs as Separated Values object
     * @param Individu[] $individus
     * @return string >>> Separated Values object
     */
    private function doFormatSeparated($individus) {
        $individus = array_map([$this, 'htmlifyActeur'], $individus);
        $results = implode($this->separator, $individus);
        return $results;
    }

    /**
     * This function format an array of acteurs as Array.
     *
     * @param Individu[] $individus
     * @return array Array of array with key => value
     */
    private function doFormatArray($individus) {
        $results = [];
        /** @var Individu $individu */
        foreach ($individus as $individu) {
            $result = [];
            $result["nom"] = $this-> htmlifyIndividu($individu);

            if ($individu->getSupannId() === null) {
                $result['alerte-supann-id'] = sprintf(
                    "Cette personne ne pourra pas utiliser l'application car il manque des informations la concernant dans %s (source code '%s').",
                    $individu->getSource(),
                    $individu->getSourceCode());
            }
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param Individu $individu
     * @return string HTML
     */
    public function htmlifyIndividu($individu)
    {
        $str = (string) $individu;
        return $str;
    }

    /**
     * @param Individu[] $individus
     * @return Individu[]
     */

    public function filter($individus) {

        $results = [];

        /** @var Individu $individu */
        foreach($individus as $individu) {

            $keep = true;
            if ($keep) $results[] = $individu;
        }
        return $results;
    }

}