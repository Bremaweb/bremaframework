<?php
namespace Facebook;

define('GRAPH_URL', 'https://graph.facebook.com/v2.6/');

class Messenger {
    private $_token = null;
    private $_error = null;

    /**
     * Messenger constructor.
     * @param string $pat Page Access Token
     */
    public function __construct($pat){
        $this->_token = $pat;
    }


    /**
     * @param int $uid
     * @param string $fields
     * @return bool|mixed
     */
    public function getProfile($uid, $fields = 'first_name,last_name,profile_pic'){
        debugLog($uid);
        $profile = $this->_executeRequest($uid . '?fields=' . $fields);
        debugLog($profile);
        return $profile;
    }

    /**
     * @param int $fbuid
     * @param string $text
     * @param string $type
     * @return bool|mixed
     */
    public function sendText($fbuid, $text, $type = 'response'){
        $data = array(
            'messaging_type' => $type,
            'recipient' => array('id' => $fbuid),
            'message' => array('text' => $text)
        );

        return $this->_executeRequest('me/messages', $data, 'POST');
    }

    /**
     * @param $fbuid
     * @return bool|mixed
     */
    public function sendRead($fbuid){
        return $this->senderAction($fbuid, 'mark_seen');
    }

    /**
     * @param $fbuid
     * @return bool|mixed
     */
    public function startTyping($fbuid){
        return $this->senderAction($fbuid, 'typing_on');
    }

    /**
     * @param $fbuid
     * @return bool|mixed
     */
    public function stopTyping($fbuid){
        return $this->senderAction($fbuid, 'typing_off');
    }

    /**
     * @return string
     */
    public function getError(){
        return $this->_error;
    }


    /**
     * @param $fbuid
     * @param $action
     * @return bool|mixed
     */
    private function senderAction($fbuid, $action){
        $data = array(
            'recipient' => array('id' => $fbuid),
            'sender_action' =>   $action
        );
        return $this->_executeRequest('me/messages', $data, 'post');
    }

    /**
     * @param $endPoint
     * @param null $data
     * @param string $method
     * @return bool|mixed
     */
    private function _executeRequest($endPoint, $data = null, $method = 'get'){
        $url = $this->_buildUrl($endPoint);
        debugLog($url);
        if ( !empty($data) ){
            $data_string = json_encode($data);
        }

        $ch = curl_init($url);

        if ( $method != "get" ){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ( !empty($data_string) ){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        }

        if ( !empty($data_string) ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );
        }

        debugLog($ch);

        $result = json_decode(curl_exec($ch),true);
        debugLog($result);

        $httpres = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        debugLog($httpres);

        if ( $httpres == '200' ){
            return $result;
        } else {
            if ( !empty($result['error']['message']) ){
                $this->_error = $result['error']['message'];
            }
            return false;   
        }

    }

    /**
     * @param $endPoint
     * @return string
     */
    private function _buildUrl($endPoint){
        return GRAPH_URL . $endPoint . ( stripos($endPoint, "?") !== false ? "&" : "?" ) . "access_token=" . $this->_token;
    }

}