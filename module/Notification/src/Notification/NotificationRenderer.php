<?php

namespace Notification;

use Notification\Entity\NotifEntity;
use UnicaenApp\Exception\LogicException;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Resolver\TemplatePathStack;

class NotificationRenderer implements RendererInterface
{
    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * NotificationRenderer constructor.
     */
    public function __construct()
    {
        $this->renderer = new PhpRenderer();
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param  string|ModelInterface   $model  A view model
     * @param  null|array|\ArrayAccess $values Values to use during rendering
     * @return string The script output.
     */
    public function render($model, $values = null)
    {
        if (! $model instanceof ViewModel) {
            throw new LogicException("ViewModel requis");
        }

        /** @var NotifEntity $entity */
        $entity = $model->getVariable('entity');
        $templateContent = $entity->getTemplate();

        $templateDir = sys_get_temp_dir();
        $templatePath = tempnam($templateDir, 'sygal_notif_template_') . '.phtml';
        $templateName = substr($templatePath, strlen($templateDir) + 1/*slash*/);
        file_put_contents($templatePath, $templateContent);

        /** @var TemplatePathStack $resolver */
        $resolver = $this->renderer->resolver();
        $resolver->addPath($templateDir);
        $this->renderer->setResolver($resolver);

        $model->setTemplate($templateName);

        return $this->renderer->render($model, $values);
    }

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return mixed
     */
    public function getEngine()
    {
        return $this->renderer->getEngine();
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @param  ResolverInterface $resolver
     * @return RendererInterface
     */
    public function setResolver(ResolverInterface $resolver)
    {
        return $this->renderer->setResolver($resolver);
    }
}