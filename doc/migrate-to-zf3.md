Migration vers ZF3
==================

Fait :
- Usages de 
    - `Zend\ServiceManager\FactoryInterface`
    - `Zend\ServiceManager\AbstractFactoryInterface`
    - `Zend\Mvc\Controller\ControllerManager`
    - `\Zend\View\HelperPluginManager`
- rechercher :
    - `(FormElementManager `
    - `use Zend\Stdlib\Hydrator\HydratorInterface` (supprimer `Stdlib\`)
Remplacer partout Zend\Mvc\Router\ par Zend\Router\.
Remplacer partout Zend\Router\Console\Simple par Zend\Mvc\Console\Router\Simple (ou carrément
Zend\Router\Console par Zend\Mvc\Console\Router ?)
Remplacer Zend\Mvc\Controller\Plugin\PostRedirectGet par Zend\Mvc\Plugin\Prg\PostRedirectGet
Remplacer Zend\Mvc\Controller\Plugin\FlashMessenger par Zend\Mvc\Plugin\FlashMessenger\FlashMessenger
Remplacer Zend\Stdlib\Hydrator par Zend\Hydrator.
      
- rechercher `implements ServiceLocatorAwareInterface` (interface disparue)
- rechercher `use ServiceLocatorAwareTrait` (disparu)
- `\Zend\ServiceManager\ServiceLocatorInterface`
- rechercher ->getServiceLocator(


      
A faire :
