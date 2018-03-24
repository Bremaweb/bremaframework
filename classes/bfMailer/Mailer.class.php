<?php

namespace bfMailer;

class Mailer {
    private $interface = null;

    public function __construct($mailerDef){
        $def = \bfMailer\MailerDef::getDef($mailerDef);

        $className = "\bfMailer\Interfaces\\" . $def['mailer'];
        if ( class_exists($className) ){
            $this->interface = new $className();
            if ( false === $this->interface->init($def['params']) ){
                throw new Exception('Unable to initialize mailer interface: ' . $this->interface->getError());
            }
        } else {
            throw new Exception('Unable to find mailer interface ' . $def['mailer']);
        }
    }

    public function send($to, $subject, $message, $attachments = array()){
        return $this->interface->send($to, $subject, $message, $attachments);
    }

    public function sendView($to, $subject, $view, $emailData, $attachments = array()){
        ob_start();
            include $view;
        $message = ob_get_clean();

        $this->send($to, $subject, $message, $attachments);
    }

    public function setFrom($fromAddress, $fromName = null){
        return $this->interface->setFrom($fromAddress, $fromName);
    }

    public function setReplyTo($replyTo){
        return $this->interface->setReplyTo($replyTo);
    }

}