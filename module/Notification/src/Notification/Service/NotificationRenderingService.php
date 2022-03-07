<?php

namespace Notification\Service;

use Notification\Notification;
use UnicaenApp\Exception\RuntimeException;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Laminas\View\Resolver\TemplatePathStack;

class NotificationRenderingService
{
    /**
     * @var PhpRenderer
     */
    protected $phpRenderer;

    /**
     * @var Notification
     */
    protected $notification;

    /**
     * Constructor.
     *
     * @param PhpRenderer $phpRenderer
     */
    public function __construct(PhpRenderer $phpRenderer)
    {
        $this->phpRenderer = $phpRenderer;
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

        return $this->phpRenderer->render($model);
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

        $resolver = $this->phpRenderer->resolver();
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
        $this->phpRenderer->setResolver($resolver);

        $model->setTemplate($template);

        $html = $this->phpRenderer->render($model);

        unlink($templatePath);

        return $html;
    }
}