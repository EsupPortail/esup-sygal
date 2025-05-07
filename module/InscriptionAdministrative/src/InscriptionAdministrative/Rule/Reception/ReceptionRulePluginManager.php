<?php

namespace InscriptionAdministrative\Rule\Reception;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRule;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRuleFactory;
use InscriptionAdministrative\Rule\Reception\StatutAutre\StatutAutreRule;
use InscriptionAdministrative\Rule\Reception\StatutAutre\StatutAutreRuleFactory;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRule;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRuleFactory;
use Laminas\ServiceManager\AbstractPluginManager;

class ReceptionRulePluginManager extends AbstractPluginManager
{
    protected $instanceOf = ReceptionRuleInterface::class;

    protected $factories = [
        StatutValideeRule::class => StatutValideeRuleFactory::class,
        StatutAnnuleeRule::class => StatutAnnuleeRuleFactory::class,
        StatutAutreRule::class => StatutAutreRuleFactory::class,
    ];

//    /**
//     * @throws \Psr\Container\ContainerExceptionInterface
//     * @throws \Psr\Container\NotFoundExceptionInterface
//     */
//    public function get($name, ?array $options = null): ReceptionRuleInterface
//    {
//        /** @var \InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface $rule */
//        $rule = parent::get($name, $options);
//
//        /** @var ModuleConfig $moduleConfig */
//        $moduleConfig = $this->creationContext->get(ModuleConfig::class);
//
//        // ...
//
//        return $rule;
//    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getRuleForInscriptionAdministrative(InscriptionAdministrative $ia): ReceptionRuleInterface
    {
        if ($ia->estValidee()) {
            $name = StatutValideeRule::class;
        } elseif ($ia->estAnnulee()) {
            $name = StatutAnnuleeRule::class;
        } else {
            $name = StatutAutreRule::class;
        }

        /** @var ReceptionRuleInterface $rule */
        $rule = $this->get($name);
        $rule->setInscriptionAdministrative($ia);

        return $rule;
    }
}