<?php

namespace Notification\Service;

use DateTime;
use Doctrine\ORM\ORMException;
use Laminas\Mail\Message;
use Notification\Entity\NotifMail;
use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\Exception\ExceptionInterface;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\MessageContainer;
use Notification\Notification;
use Notification\NotificationResult;
use Throwable;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\Service\Mailer\MailerServiceAwareTrait;

/**
 * Service d'envoi de notification par mail.
 *
 * @author Unicaen
 */
final class NotifierService
{
    use NotifEntityServiceAwareTrait;
    use MailerServiceAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * @var \Notification\MessageContainer
     * @deprecated
     */
    protected MessageContainer $messageContainer;

    protected NotificationRenderingService $renderingService;
    protected NotificationFactory $notificationFactory;

    protected array $defaultOptions = [
        // préfixe à ajouter systématiquement devant le sujet des mails, ex: '[Sygal] '
        'subject_prefix' => '',

        // destinataires à ajouter systématiquement en copie conforme ou cachée de tous les mails
        'cc'  => [],
        'bcc' => [],
    ];

    protected array $options = [];

    /**
     * NotifierService constructor.
     */
    public function __construct(NotificationRenderingService $renderingService)
    {
        $this->renderingService = $renderingService;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function trigger(Notification $notification): NotificationResult
    {
        $result = new NotificationResult($notification);

        $notification->prepare();
        try {
            $message = $this->sendNotification($notification);
            $sendDate = $this->extractDateFromMessage($message) ?: new DateTime();
            $result
                ->setSendDate($sendDate)
                ->setIsSuccess()
                ->setSuccessMessages($notification->getSuccessMessages());
        } catch (ExceptionInterface $e) {
            $result
                ->setIsSuccess(false)
                ->setErrorMessages([$e->getMessage()]);
        }

        return $result;
    }

    protected function sendNotification(Notification $notification): Message
    {
        $email = $this->createMailMessageForNotification($notification);

        $this->saveNotifMail($email);

        try {
            $message = $this->mailerService->send($email);
        } catch (Throwable $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'envoi de la notification", null, $e);
        }

        return $message;
    }

    /**
     * todo: mécanisme d'événement, ou bien passer à unicaen/mail.
     */
    private function saveNotifMail(Message $email)
    {
        $nMail = new NotifMail();

        $mails = [];
        foreach ($email->getFrom() as $mail) { $mails[] = $mail->getEmail(); }
        $nMail->setFrom(implode(',', $mails));
        $mails = [];
        foreach ($email->getTo() as $mail) { $mails[] = $mail->getEmail(); }
        if ($mails === []) $mails = ["unknown"];
        $nMail->setTo(implode(',', $mails));
        $nMail->setSubject($email->getSubject());
        $body = $email->getBodyText();
//        $body = htmlentities($body);
        $nMail->setBody($body);
        $nMail->setSentOn(new DateTime());
        try {
            $this->entityManager->persist($nMail);
            $this->entityManager->flush($nMail);
        } catch (ORMException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement dans NotifMail", null, $e);
        }
    }

    private function extractDateFromMessage(Message $message): ?DateTime
    {
        if (!$message->getHeaders()->has('Date')) {
            return null;
        }

        $messageDate = $message->getHeaders()->get('Date')->getFieldValue();

        return date_create($messageDate) ?: null;
    }

    protected function createMailMessageForNotification(Notification $notification): Message
    {
        $subjectPrefix = '';
        if (isset($this->options['subject_prefix'])) {
            $subjectPrefix = $this->options['subject_prefix'];
        }

        $subject = trim($subjectPrefix) . " " . $notification->getSubject();
        $to = $notification->getTo();
        $cc = $notification->getCc();
        $bcc = $notification->getBcc();

        $html = $this->renderingService->setNotification($notification)->render();

        $mail = $this->mailerService->createNewMessage($html, $subject);
        $mail->setTo($to);

        if ($cc AND $cc !== []) {
            $mail->setCc($cc);
        }
        if ($bcc AND $bcc !== []) {
            $mail->setBcc($bcc);
        }

        if (isset($this->options['cc'])) {
            $mail->addBcc($this->options['cc']);
        }
        if (isset($this->options['bcc'])) {
            $mail->addBcc($this->options['bcc']);
        }

        return $mail;
    }
}