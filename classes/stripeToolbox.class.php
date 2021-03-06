<?php

class stripeToolbox {

    private $_key = null;
    private $_error = "";

    public function __construct($key){
        \Stripe\Stripe::setApiKey(STRIPE_KEY);
        $this->_key = $key;
    }

    public function getError(){
        return $this->_error;
    }

    /**
     * @param array $cData
     * @return \Stripe\Customer
     */
    public function createCustomer($cData){
        try {
            return \Stripe\Customer::create($cData);
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param string $id
     * @return bool|\Stripe\Customer
     */
    public function getCustomer($id){
        try {
            return \Stripe\Customer::retrieve($id);
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id
     * @return bool|\Stripe\Plan
     */
    public function getPlan($id){
        try {
            $plan = \Stripe\Plan::retrieve($id);
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
        return $plan;
    }

    public function addSubscription($customerId, $plans){
        if ( !is_array($plans) ){
            $plans = array($plans);
        }
        $sData = array(
            'customer' => $customerId,
            'items' => array()
        );
        foreach ( $plans as $plan ){
            $sData['items'][] = array('plan' => $plan);
        }

        return \Stripe\Subscription::create($sData);
    }

    public function createPlan($pData){
        return \Stripe\Plan::create($pData);
    }

    /**
     * @param Stripe\Customer $customer
     * @param string $planId
     * @return bool
     */
    public function customerHasSubscription(Stripe\Customer $customer, $planId){
        if ( !empty($customer->subscriptions->data) ){
            foreach ( $customer->subscriptions->data as $subscription ){
                if ( $subscription->status == 'active' && $subscription->plan->id == $planId ){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param \Stripe\Customer $customer
     * @param string $planId
     * @return Stripe\Subscription
     */
    public function getSubscription(Stripe\Customer $customer, $planId){
        if ( !empty($customer->subscriptions->data) ){
            foreach ( $customer->subscriptions->data as $subscription ){
                if ( $subscription->status == 'active' && $subscription->plan->id == $planId ){
                    return $subscription;
                }
            }
        }
        return false;
    }

    public function getSubscriptionById($subId){
        try {
            $sub = \Stripe\Subscription::retrieve($subId);
            return $sub;
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    public function getToken($code){
        $url = "https://connect.stripe.com/oauth/token";
        $params = array(
            'client_secret' => $this->_key,
            'code' => $code,
            'grant_type' => 'authorization_code'
        );

        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $params);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return json_decode($result);
    }

    /**
     * @param $id
     * @return bool|\Stripe\Account
     */
    public function getAccount($id){
        try {
            $acc = \Stripe\Account::retrieve($id);
            return $acc;
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $id
     * @return bool|\Stripe\Charge
     */
    public function getCharge($id){
        try {
            return \Stripe\Charge::retrieve($id);
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }

    }

    /**
     * @param $amount
     * @return bool
     */
    public function payout($amount){
        try {
            \Stripe\Payout::create(array(
                'amount' => $amount,
                'currency' => 'usd'
            ));
            return true;
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $account
     * @param $amount
     * @return bool|\Stripe\Transfer
     */
    public function transfer($account, $amount){
        try {
            return \Stripe\Transfer::create(array(
                'amount' => $amount,
                'destination' => $account,
                'currency' => 'usd'
            ));
        } catch ( Exception $e ){
            $this->_error = $e->getMessage();
            return false;
        }
    }

    /**
     * @return \Stripe\Balance
     */
    public function getBalance(){
        return \Stripe\Balance::retrieve();
    }

    /**
     * @param $amount
     * @param $source
     * @param $email
     * @param $title
     * @param string $currency
     * @return bool|\Stripe\Charge
     */
    public function createCharge($amount, $source, $email, $title, $currency = 'usd'){
        try {
            $chargeData = array(
                'amount' => $amount,
                'currency' => $currency,
                'description' => $title,
                'receipt_email' => $email,
                'statement_descriptor' => $title
            );

            if ( is_object($source) && !empty($source->id) ){
                $chargeData['customer'] = $source->id;
            } else if ( is_string($source) ) {
                $chargeData['source'] = $source;
            } else {
                throw new Exception('Invalid source');
            }

            $charge = \Stripe\Charge::create($chargeData);
            return $charge;
        } catch ( Exception $e ){
           $this->_error = $e->getMessage();
           return false;
        }
    }
}