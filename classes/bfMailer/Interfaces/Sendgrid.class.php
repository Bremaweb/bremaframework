<?php
namespace bfMailer\Interfaces {
    require_once BREMA_DIR . 'classes/sendgrid-php/sendgrid-php.php';

    class Sendgrid extends MailerInterface {
        private $_sendgrid;
        private $_key;
        private $_from;
        private $_replyTo;

        public function init($params){
            if ( !empty($params['key']) ){
                $this->_key = $params['key'];
            } else {
                $this->_error = 'Please provide an API key';
                return false;
            }

            try {
                $this->_sendgrid = new \SendGrid($this->_key);
            } catch ( Exception $e ){
                $this->_error = $e->getMessage();
                return false;
            }
        }

        public function send($to, $subject, $message, $attachments){
            $email = new \SendGrid\Mail\Mail();
            $email->addTo($to);
            $email->setFrom($this->_from);
            $email->setSubject($subject);
            $email->addContent('text/plain', strip_tags($message));
            $email->addContent('text/html', $message);

            try {
                $response = $this->_sendgrid->send($email);
                print $response->statusCode() . "\n";
                print_r($response->headers());
                print $response->body() . "\n";
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."\n";
            }
        }

        public function setFrom($from){
            $this->_from = $from;
        }

        public function setReplyTo($replyTo){
            $this->_replyTo = $replyTo;
        }
    }
}