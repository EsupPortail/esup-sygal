<?php

namespace Application\Filter;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\View\Renderer\PhpRenderer;
use Zend\Filter\AbstractFilter;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\View\Helper\HtmlList;

class RolesUtilisateurFormatter extends AbstractFilter
{
    /**
     * @var bool
     */
    private $asUl = false;

    /**
     * @var bool
     */
    private $asSeparated = true;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string
     */
    private $role;

    /**
     * @param RoleInterface|string $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return $this
     */
    public function asUl()
    {
        $this->asUl = true;
        $this->asSeparated = false;

        return $this;
    }

    /**
     * @param string $separator
     * @return $this
     */
    public function asSeparated($separator = ", ")
    {
        $this->asUl = false;
        $this->asSeparated = true;
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  Utilisateur $value Utilisateur
     * @return string
     */
    public function filter($value)
    {
        if ($value instanceof Utilisateur) {
            $roles = $value->getRoles()->toArray();
        }
        else {
            throw new \LogicException("Cas inattendu!");
        }

        if ($this->role) {
            $roleId = $this->role instanceof RoleInterface ? $this->role->getRoleId() : $this->role;
            $roles = array_filter($roles, function (Role $r) use ($roleId) {
                return $roleId === $r->getRoleId();
            });
        };

        if (count($roles) === 0) {
            return '';
        }

        return $this->doFormat($roles);
    }

    private function doFormat($roles = [])
    {
        if ($this->asUl) {
            $helper = new HtmlList();
            $helper->setView(new PhpRenderer());
            $result = $helper($roles, $ordered = false, $attribs = false, $escape = false);
        }
        elseif ($this->asSeparated) {
            $result = implode($this->separator, $roles);
        }
        else {
            throw new \LogicException("Cas inattendu !");
        }

        return $result;
    }
}