<?php

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\PaymentIntent;

defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payments_model');
    }

    public function index($action = null)
    {
        show_404();
    }

    public function log_payment($msg, $val = null)
    {
        $this->load->library('logs');
        return (bool) $this->logs->write('payments', $msg, $val);
    }

    public function paypalipn()
    {
        $this->log_payment('Paypal IPN called');

        //$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
        $fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

        if (!$fp) {
            $this->log_payment('Paypal Payment Failed (IPN HTTP ERROR)', $errstr);
        } else {
            $paypal = $this->payments_model->getPaypalSettings();
            if (!empty($_POST)) {
                $req = 'cmd=_notify-validate';
                foreach ($_POST as $key => $value) {
                    $value = urlencode(stripslashes($value));
                    $req .= "&$key=$value";
                }

                $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $header .= "Host: www.paypal.com\r\n";  // www.sandbox.paypal.com for a test site
                $header .= 'Content-Length: ' . strlen($req) . "\r\n";
                $header .= "Connection: close\r\n\r\n";

                fputs($fp, $header . $req);
                while (!feof($fp)) {
                    $res = fgets($fp, 1024);
                    //log_message('error', 'Paypal IPN - fp handler -'.$res);
                    if (stripos($res, 'VERIFIED') !== false) {
                        $this->log_payment('Paypal IPN - VERIFIED');

                        $custom      = explode('__', $_POST['custom']);
                        $payer_email = $_POST['payer_email'];

                        if (($_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Processed' || $_POST['payment_status'] == 'Pending') && ($_POST['receiver_email'] == $paypal->account_email) && ($_POST['mc_gross'] == ($custom[1] + $custom[2]))) {
                            $invoice_no = $_POST['item_number'];
                            $reference  = $_POST['item_name'];
                            $amount     = $_POST['mc_gross'];

                            if ($inv = $this->payments_model->getInvoiceByID($invoice_no)) {
                                $note = $_POST['mc_currency'] . ' ' . $_POST['mc_gross'] . ' had been paid for the Sale No. ' . $inv->id . ' (Reference No ' . $reference . '). Paypal transaction id is ' . $_POST['txn_id'];
                                if ($this->payments_model->addPayment($inv->id, $inv->customer_id, $amount, $note)) {
                                    $this->send_email($paypal->account_email, $inv->id, $inv->customer_id, $amount, $note);
                                    $this->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Paypal (' . $_POST['txn_id'] . ').', print_r($_POST, true));
                                }
                            }
                        } else {
                            $this->log_payment('Payment failed via Paypal, please check manually. ', (!empty($_POST) ? print_r($_POST, true) : null));
                        }
                    } elseif (stripos($res, 'INVALID') !== false) {
                        $this->log_payment('INVALID response from Paypal. Payment failed. ', (!empty($_POST) ? print_r($_POST, true) : null));
                    }
                }
                fclose($fp);
            } else {
                $this->log_payment('INVALID response from Paypal (no post data received). Payment failed. ', (!empty($_POST) ? print_r($_POST, true) : null));
            }
        }
    }

    public function send_email($email, $invoice_id, $customer_id, $amount, $note)
    {
        $settings = $this->site->get_setting();
        $customer = $this->payments_model->getCustomerByID($customer_id);
        $msg      = '<html><body>Hello ' . ($customer->company ? $customer->company : $customer->name) . ', <br><br>' . $note . '<br><br>Thank you<br>' . $settings->site_name . '</body></html>';

        $this->load->library('email');
        //$config['protocol'] = 'sendmail';
        //$config['mailpath'] = '/usr/sbin/sendmail';
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        $this->email->from($email);
        $this->email->to($customer->email);
        $this->email->bcc($email);
        $this->email->subject('Payment Received');
        $this->email->message($msg);
        $this->email->send();
    }

    public function skrillipn()
    {
        $skrill = $this->payments_model->getSkrillSettings();
        $this->log_payment('Skrill IPN called');
        if (!empty($_POST)) {
            // Validate the skrill signatrue
            $concatFields = $_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper(md5($skrill->secret_word)) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status'];
            // Ensure the signatrue is valid, the status code == 2, and that the money is paid to you
            if (strtoupper(md5($concatFields)) == $_POST['md5sig'] && $_POST['status'] == 2 && $_POST['pay_to_email'] == $skrill->account_email) {
                $invoice_no = $_POST['item_number'];
                $reference  = $_POST['item_name'];
                $amount     = $_POST['mb_amount'];

                if ($inv = $this->payments_model->getInvoiceByID($invoice_no)) {
                    $note = $_POST['mb_currency'] . ' ' . $_POST['mb_amount'] . ' had been paid for the Sale No. ' . $inv->id . ' (Reference No ' . $reference . '). Skrill transaction id is ' . $_POST['mb_transaction_id'];

                    if ($this->payments_model->addPayment($inv->id, $inv->customer_id, $amount, $note)) {
                        $this->send_email($skrill->account_email, $inv->id, $inv->customer_id, $amount, $note);
                        $this->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Skrill (' . $_POST['mb_transaction_id'] . ').', print_r($_POST, true));
                    }
                }
            } else {
                $this->log_payment('Payment failed via Skrill, please check manually. ', (!empty($_POST) ? print_r($_POST, true) : null));
                exit;
            }
        } else {
            $this->log_payment('INVALID response from Skrill (no post data received). Payment failed ', (!empty($_POST) ? print_r($_POST, true) : null));
            exit;
        }
    }

    public function stripe($id = '', $eid = null)
    {
        if (base64_decode($eid) != $id) {
            $this->session->set_flashdata('message', lang('invalid_signature'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->model('sales_model');
        $inv  = $this->sales_model->getInvoiceByID($id);
        $paid = $this->sales_model->getPaidAmount($id);

        if (($inv->grand_total <= $paid)) {
            $this->session->set_flashdata('message', lang('invoice_already_paid'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['inv']      = $inv;
        $this->data['paid']     = $paid;
        $this->data['settings'] = $this->site->get_setting();
        $this->data['stripe']   = $this->sales_model->getStripeSettings();
        $this->load->view($this->data['settings']->theme . '/views/payments/stripe', $this->data);
    }

    public function stripe_create($id)
    {
        $this->load->model('sales_model');
        $settings = $this->site->get_setting();
        $inv      = $this->sales_model->getInvoiceByID($id);
        $paid     = $this->sales_model->getPaidAmount($id);
        $stripe   = $this->sales_model->getStripeSettings();
        $customer = $this->sales_model->getCustomerByID($inv->customer_id);
        $biller   = $this->sales_model->getCompanyByID($inv->company_id ? $inv->company_id : 1);

        Stripe::setApiKey($stripe->secret_key);
        header('Content-Type: application/json');

        try {
            $stripe_fee  = 0;
            $grand_total = ($inv->grand_total - $paid);
            if (trim(strtolower($customer->country)) == $biller->country) {
                $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_my / 100);
            } else {
                $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_other / 100);
            }

            $json_str      = file_get_contents('php://input');
            $json_obj      = json_decode($json_str);
            $paymentIntent = PaymentIntent::create([
                'amount'   => ($grand_total + $stripe_fee) * 100,
                'currency' => $settings->currency_prefix,
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            echo json_encode($output);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            echo json_encode(['error' => $e->getMessage()]);
            // http_response_code(500);
        } catch (\Error $e) {
            echo json_encode(['error' => $e->getMessage()]);
            // http_response_code(500);
        }
    }

    public function stripe_done($id)
    {
        if (!$this->input->post('paymentIntentId')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => lang('no_id_provided')]);
            die();
        }
        $paymentIntentId = $this->input->post('paymentIntentId');

        $this->load->model('sales_model');
        $settings = $this->site->get_setting();
        $inv      = $this->sales_model->getInvoiceByID($id);
        $paid     = $this->sales_model->getPaidAmount($id);
        $stripe   = $this->sales_model->getStripeSettings();
        $customer = $this->sales_model->getCustomerByID($inv->customer_id);
        $biller   = $this->sales_model->getCompanyByID($inv->company_id ? $inv->company_id : 1);

        $stripe_fee  = 0;
        $grand_total = ($inv->grand_total - $paid);
        if (trim(strtolower($customer->country)) == $biller->country) {
            $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_my / 100);
        } else {
            $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_other / 100);
        }

        $amount = ($grand_total + $stripe_fee) * 100;

        $stripe_client = new StripeClient($stripe->secret_key);
        $payment       = $stripe_client->paymentIntents->retrieve($paymentIntentId, []);
        if ($payment->amount == $amount && $payment->amount_received == $amount && mb_strtolower($payment->currency) == mb_strtolower($settings->currency_prefix)) {
            $amount = $amount / 100;
            $note   = 'Payment has been made for Sale Reference #' . $inv->reference_no . ' via Stripe (' . $payment->id . ').';
            if ($this->payments_model->addPayment($inv->id, $inv->customer_id, $amount, $note)) {
                if ($customer->email) {
                    $this->send_email($customer->email, $inv->id, $inv->customer_id, $amount, $note);
                }
                $this->log_payment($note, print_r($payment, true));
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $note]);
                die();
            }
        }
        echo json_encode(['success' => false, 'error' => lang('failed_to_get_payment_details')]);
        die();
    }
}
