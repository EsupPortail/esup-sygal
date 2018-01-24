<?php

namespace Application\View\Helper;

class EscapeTextHelper extends AbstractHelper
{
    /**
     * Remplacement des sauts de ligne par <br>.
     * Echappement HTML.
     *
     * @param string|null $value
     * @return string
     */
    public function render($value = null)
    {
        return preg_replace("/\r\n|\n|\r/", '<br>', $this->getView()->escapeHtml($value ?: $this->value));
    }
}