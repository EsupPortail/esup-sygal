<?php

namespace Notification;

use Notification\Entity\NotifEntity;
use Zend\View\Renderer\PhpRenderer;
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
     */
    public function __construct()
    {
        $this->renderer = new PhpRenderer();
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
            return $this->renderUsingNotifEntity($entity);
        }

        $template = $this->notification->getTemplatePath();

        $model = $this->notification->createViewModel();
        $model->setTemplate($template);

        return $this->renderer->render($model);
    }

    /**
     * @param NotifEntity $entity
     * @return string
     */
    private function renderUsingNotifEntity(NotifEntity $entity)
    {
        $model = $this->notification->createViewModel();

        $templateDir = sys_get_temp_dir();
        $templatePath = tempnam($templateDir, 'sygal_notif_template_') . '.phtml';
        $template = substr($templatePath, strlen($templateDir) + 1/*slash*/);
        file_put_contents($templatePath, $entity->getTemplate());

        /** @var TemplatePathStack $resolver */
        $resolver = $this->renderer->resolver();
        $resolver->addPath($templateDir);
        $this->renderer->setResolver($resolver);

        $model->setTemplate($template);

        $html = $this->renderer->render($model);

        unlink($templatePath);

        return $html;
    }
}