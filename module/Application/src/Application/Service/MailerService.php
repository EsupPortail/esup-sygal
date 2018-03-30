<?php

namespace Application\Service;

use UnicaenApp\Exception\LogicException;
use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Mime;
use Zend\Mime\Part;
use Zend\Mime\Message as MimeMessage;

/**
 * Service d'envoi de mail.
 *
 * @author Unicaen
 */
class MailerService
{
    const SUBJECT_SUFFIX = ' {REDIR}';
    const CURRENT_USER = 'CURRENT_USER';
    const BODY_TEXT_TEMPLATE = <<<EOS

-----------------------------------------------------------------------
Ce mail a été redirigé.
Destinataires originaux :
To: %s
Cc: %s
Bcc: %s
EOS;
    const BODY_HTML_TEMPLATE = <<<EOS
<p>Ce mail a été redirigé.</p>
<p>
Destinataires originaux :<br />
To: %s<br />
Cc: %s<br />
Bcc: %s
</p>
EOS;
//    const CURRENT_USER = 'CURRENT_USER';

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var array
     */
    protected $redirectTo = [];

    /**
     * @var bool
     */
    protected $doNotSend = false;

    /**
     * @var \UnicaenApp\Entity\Ldap\People
     */
    protected $identity;

    /**
     * Constructeur.
     *
     * @param TransportInterface $transport Mode de transport à utiliser
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Méthode utilitaire pour instancier un nouveau message ayant un corps au format HTML.
     * Car c'est ce qu'on fait la plupart du temps dans nos applications !
     *
     * @param string $htmlBody HTML du corps de mail
     * @param string $subject  Sujet du mail
     * @param string $from Adresse de l'expéditeur, 'ne_pas_repondre@unicaen.fr' par défaut
     * @return Message
     */
    public function createNewMessage($htmlBody, $subject, $from = 'ne_pas_repondre@unicaen.fr')
    {
        // corps au format HTML
        $html = $htmlBody;
        $part = new Part($html);
        $part->type = Mime::TYPE_HTML;
        $part->charset = 'UTF-8';
        $body = new MimeMessage();
        $body->addPart($part);

        return (new Message())
            ->setEncoding('UTF-8')
            ->setFrom($from)
            ->setSubject($subject)
            ->setBody($body);
    }

    /**
     * Envoit le message.
     *
     * @param Message $message Message à envoyer
     * @return Message Message effectivement envoyé, différent de l'original si la redirection est activée
     */
    public function send(Message $message)
    {
        $msg = $this->prepareMessage($message);

        if (!$this->getDoNotSend()) {
            $this->transport->send($msg);
        }

        return $msg;
    }

    /**
     *
     * @param Message $message
     * @return Message
     */
    protected function prepareMessage(Message $message)
    {
        if (!$this->getRedirectTo()) {
            return $message;
        }

        // collecte des destinataires originaux pour les afficher à la fin du mail
        $to  = [];
        $cc  = [];
        $bcc = [];
        foreach ($message->getTo() as $addr) { /* @var $addr \Zend\Mail\Address */
            $to[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        foreach ($message->getCc() as $addr) { /* @var $addr \Zend\Mail\Address */
            $cc[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        foreach ($message->getBcc() as $addr) { /* @var $addr \Zend\Mail\Address */
            $bcc[] = $addr->getEmail() . ($addr->getName() ? ' <' . $addr->getName() . '>' : null);
        }
        $to   = implode(", ", $to);
        $cc   = implode(", ", $cc);
        $bcc  = implode(", ", $bcc);
        $body = $message->getBody();

        /**
         * Si corps de mail en HTML
         */
        if ($body instanceof \Zend\Mime\Message) {
            $template = self::BODY_HTML_TEMPLATE;
            $part = new Part(sprintf($template, $to, $cc, $bcc));
            $part->type = Mime::TYPE_HTML;
            $part->charset = $message->getEncoding();
            $body->addPart($part);
        }
        /**
         * Si corps de mail texte ou autre
         */
        else {
            $template = self::BODY_TEXT_TEMPLATE;
            $body .= sprintf($template, $to, $cc, $bcc);
        }

        // 'CURRENT_USER' dans les adresses de redirection n'est plus supportée
        foreach ($redirectTo = $this->getRedirectTo() as $key => $value) {
            if (self::CURRENT_USER === $key || self::CURRENT_USER === $value) {
                throw new LogicException("La valeur 'CURRENT_USER' n'est plus supportée par le mécanisme de redirection de mail");
            }
        }

        $msg = new Message();
        $msg->setSubject($message->getSubject() . self::SUBJECT_SUFFIX)
            ->setFrom($message->getFrom())
            ->setTo($this->getRedirectTo())
            ->setCc([])
            ->setBcc([])
            ->setBody($body)
            ->setEncoding($message->getEncoding());

        return $msg;
    }

    /**
     * Retourne le mode de transport à utiliser.
     *
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Spécifie le mode de transport à utiliser.
     *
     * @param TransportInterface $transport
     * @return self
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Retourne les adresses vers lesquelles rediriger les mails.
     * NB: elles sont substituées aux adresses originales.
     *
     * @return array
     */
    public function getRedirectTo()
    {
        return $this->redirectTo ? (array)$this->redirectTo : [];
    }

    /**
     * Spécifie les adresses vers lesquelles rediriger les mails.
     * NB: elles sont substituées aux adresses originales.
     *
     * @param array|string $redirectTo Tableau d'emails, ou emails séparés par une virgule
     * @return self
     */
    public function setRedirectTo($redirectTo)
    {
        $this->redirectTo = [];
        $this->addRedirectTo($redirectTo);
        return $this;
    }

    /**
     * Ajoute des adresses vers lesquelles rediriger les mails.
     *
     * @param array|string $redirectTo Tableau d'emails, ou emails séparés par une virgule
     * @return self
     */
    public function addRedirectTo($redirectTo)
    {
        if (is_string($redirectTo)) {
            $redirectTo = array_map('trim', explode(',', $redirectTo));
        }
        $this->redirectTo = array_merge($this->redirectTo, $redirectTo);
        return $this;
    }

    /**
     * Retourne le flag indiquant si l'envoi des mails est désactivé.
     *
     * @return bool
     */
    public function getDoNotSend()
    {
        return $this->doNotSend;
    }

    /**
     * Spécifie le flag indiquant si l'envoi des mails est désactivé.
     *
     * @param bool $doNotSend
     * @return self
     */
    public function setDoNotSend($doNotSend = true)
    {
        $this->doNotSend = (bool)$doNotSend;
        return $this;
    }
}
