<?php

namespace bfMailer\Interfaces;

class phpMailer extends MailerInterface {
    private $mailer;

    public function init($params){
        try {
            $this->mailer = new \PHPMailer();

            $this->mailer->IsSMTP();
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $this->mailer->Host = value($params, 'host', '');
            $this->mailer->Port = value($params, 'port', '');
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPSecure = value($params, 'secure', null);
            $this->mailer->Username = value($params, 'username', '');
            $this->mailer->Password = value($params, 'password', '');
            //$this->mailer->SMTPDebug = 1;
            $this->mailer->SetFrom(value($params, 'from', ''), value($params, 'from_name', null));
            if ( !empty($params['reply_to']) ){
                $this->mailer->addReplyTo(value($params, 'reply_to', null));
            }
        } catch ( Exception $e ){
            return false;
        }
    }

    public function send($to, $subject, $message, $attachments = array()){
        $this->mailer->clearAddresses();
        $this->mailer->addAddress($to);

        $this->mailer->Subject = $subject;

        $this->mailer->msgHTML($message);

        $this->mailer->AltBody = strip_tags($message);

        if ( !empty($attachments) ){
            foreach ( $attachments as $attachment ){
                $this->mailer->addAttachment($attachment);
            }
        }

        if ( !$this->mailer->Send() ){
           throw new \Exception($this->mailer->ErrorInfo);
        }
    }

    public function setFrom($fromAddress, $fromName = null){
        $this->mailer->setFrom($fromAddress, $fromName);
    }

    public function setReplyTo($replyTo){
        $this->mailer->clearReplyTos();
        $this->mailer->addReplyTo($replyTo);
    }

}