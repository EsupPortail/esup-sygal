<?php

namespace Notification\Service;

use Notification\MessageContainer;
use Notification\Notification;
use Notification\Service\Mailer\MailerServiceAwareTrait;
use Zend\View\Helper\Url as UrlHelper;
use Zend\View\Renderer\RendererInterface;

/**
 * Service de construction et d'envoi de notifications par mail.
 *
 * @author Unicaen
 */
class NotifierService
{
    use MailerServiceAwareTrait;

    /**
     * @var MessageContainer
     */
    protected $messageContainer;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var array
     */
    protected $defaultOptions = [
        // préfixe à ajouter systématiquement devant le sujet des mails, ex: '[Sygal] '
        'subject_prefix' => '',

        // destinataires à ajouter systématiquement en copie conforme ou cachée de tous les mails
        'cc'  => [],
        'bcc' => [],
    ];

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
        $this->messageContainer = new MessageContainer();
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function trigger(Notification $notification)
    {
        $notification->prepare();
        $html = $this->renderNotification($notification);

        $subjectPrefix = '';
        if (isset($this->options['subject_prefix'])) {
            $subjectPrefix = $this->options['subject_prefix'];
        }

        $subject = trim($subjectPrefix) . " " . $notification->getSubject();
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

        if (isset($this->options['cc'])) {
            $mail->addBcc($this->options['cc']);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        $this->mailerService->send($mail);

        // collecte des éventuels messages exposés par la notification
        foreach ($notification->getInfoMessages() as $message) {
            $this->messageContainer->setMessage($message, 'info');
        }
        foreach ($notification->getWarningMessages() as $message) {
            $this->messageContainer->setMessage($message, 'warning');
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
     * Retourne les éventuels messages exposés lors de la notification.
     *
     * @return array
     * @see MessageContainer::getMessages()
     */
    public function getLogs()
    {
        return $this->messageContainer->getMessages();
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }
}