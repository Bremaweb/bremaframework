<?php

namespace bfMailer\Interfaces;

abstract class MailerInterface {
    protected $_error = '';

    public function getError(){
        return $this->_error;
    }

    abstract protected function init($params);
    abstract protected function send($to, $subject, $message, $attachments);
    abstract protected function setFrom($from);
    abstract protected function setReplyTo($replyTo);
}