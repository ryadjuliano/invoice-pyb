<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stripe_pay
{
    public $code;

    public $error = false;

    public $message = '';

    protected $private_key;

    public function __construct()
    {
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function charge($token, $description, $amount, $currency)
    {
        return $this->insert($token, $description, $amount, $currency);
    }

    public function get_balance()
    {
        try {
            $bal = \Stripe\Balance::retrieve();
            return ['mode' => ($bal->livemode ? $bal->livemode : 'Test'), 'pending_amount' => ($bal->pending[0]->amount / 100), 'pending_currency' => strtoupper($bal->pending[0]->currency), 'available_amount' => ($bal->available[0]->amount / 100), 'available_currency' => strtoupper($bal->available[0]->currency)];
        } catch (Exception $e) {
            $this->error   = true;
            $this->message = $e->getMessage();
            $this->code    = $e->getCode();
            //return FALSE;
            return ['error' => true, 'code' => $this->code, 'message' => $this->message];
        }
    }

    public function insert($token, $description, $amount, $currency)
    {
        try {
            $charge = \Stripe\Charge::create([
                'amount'      => $amount,
                'currency'    => $currency,
                'card'        => $token,
                'description' => $description
            ]);
            return $charge;
        } catch (Exception $e) {
            $this->error   = true;
            $this->message = $e->getMessage();
            $this->code    = $e->getCode();
            //return FALSE;
            return ['error' => true, 'code' => $this->code, 'message' => $this->message];
        }
    }

    public function set_api_key($private_key)
    {
        \Stripe\Stripe::setApiKey($private_key);
    }
}
