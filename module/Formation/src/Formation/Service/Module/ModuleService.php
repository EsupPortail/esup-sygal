<?php

namespace Formation\Service\Module;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Module;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ModuleService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Module $module
     * @return Module
     */
    public function create(Module $module) : Module
    {
        try {
            $this->getEntityManager()->persist($module);
            $this->getEntityManager()->flush($module);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Module]",0, $e);
        }
        return $module;
    }

    /**
     * @param Module $module
     * @return Module
     */
    public function update(Module $module) : Module
    {
        try {
            $this->getEntityManager()->flush($module);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Module]",0, $e);
        }
        return $module;
    }

    /** (todo ...)
     * @param Module $module
     * @return Module
     */
    public function historise(Module $module) : Module
    {
        try {
            $module->setHistoDestruction(new DateTime());
            $this->getEntityManager()->flush($module);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Module]",0, $e);
        }
        return $module;
    }

    /**
     * @param Module $module
     * @return Module
     */
    public function restore(Module $module) : Module
    {
        try {
            $module->setHistoDestructeur(null);
            $module->setHistoDestruction(null);
            $this->getEntityManager()->flush($module);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Module]",0, $e);
        }
        return $module;
    }

    /**
     * @param Module $module
     * @return Module
     */
    public function delete(Module $module) : Module
    {
        try {
            $this->getEntityManager()->remove($module);
            $this->getEntityManager()->flush($module);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Module]",0, $e);
        }
        return $module;
    }

    /** FACADE ********************************************************************************************************/

}