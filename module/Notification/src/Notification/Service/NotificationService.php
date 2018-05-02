<?php

namespace Notification\Service;

use Notification\Notification;
use Notification\Service\Mailer\MailerServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;
use Zend\View\Helper\Url as UrlHelper;
use Zend\View\Renderer\RendererInterface;

/**
 * Service d'envoi de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationService
{
    use MailerServiceAwareTrait;
    use MessageAwareTrait;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * NotificationService constructor.
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function trigger(Notification $notification)
    {
        $notification->prepare();
        $html = $this->renderNotification($notification);

        $subject = "[SyGAL] " . $notification->getSubject();
        $to = $notification->getTo();
        $cc = $notification->getCc();
        $bcc = $notification->getBcc();

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);
        if ($cc) {
            $mail->setCc($cc);
        }
        if ($bcc) {
            $mail->setBcc($bcc);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        $this->mailerService->send($mail);

        if ($messages = $notification->getInfoMessages()) {
            $this->setMessage(current($messages), 'info');
        }
    }

    /**
     * @param Notification $notification
     * @return string
     */
    private function renderNotification(Notification $notification)
    {
        $viewModel = $notification->createViewModel();

        $html = $this->renderer->render($viewModel);

        return $html;
    }

    /**
     * @return array
     * @see MessageAwareTrait::getMessages()
     */
    public function getLogs()
    {
        return $this->getMessages();
    }

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }
}