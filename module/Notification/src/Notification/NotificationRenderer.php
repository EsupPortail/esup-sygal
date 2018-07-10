<?php

namespace Notification;

use UnicaenApp\Exception\RuntimeException;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplatePathStack;

class NotificationRenderer
{
    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * @var Notification
     */
    protected $notification;

    /**
     * NotificationRenderer constructor.
     *
     * @param PhpRenderer $renderer
     */
    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param Notification $notification
     * @return self
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $entity = $this->notification->getNotifEntity();

        if ($entity !== null) {
            return $this->renderUsingTemplateContent($entity->getTemplate());
        }

        $template = $this->notification->getTemplatePath();

        $model = $this->notification->createViewModel();
        $model->setTemplate($template);

        return $this->renderer->render($model);
    }

    /**
     * @param String $templateContent
     * @return string
     */
    private function renderUsingTemplateContent($templateContent)
    {
        $model = $this->notification->createViewModel();

        $templateDir = sys_get_temp_dir();
        $templatePath = tempnam($templateDir, 'sygal_notif_template_') . '.phtml';
        $template = substr($templatePath, strlen($templateDir) + 1/*slash*/);
        file_put_contents($templatePath, $templateContent);

        $resolver = $this->renderer->resolver();
        if ($resolver instanceof TemplatePathStack) {
            $resolver->addPath($templateDir);
        } elseif ($resolver instanceof AggregateResolver) {
            $stack = new TemplatePathStack();
            $stack->addPath($templateDir);
            $resolver->attach($stack);
        } else {
            throw new RuntimeException(
                sprintf("Resolver rencontré inattendu (%s), impossible de générer le corps du mail", get_class($resolver)));
        }
        $this->renderer->setResolver($resolver);

        $model->setTemplate($template);

        $html = $this->renderer->render($model);

        unlink($templatePath);

        return $html;
    }
}