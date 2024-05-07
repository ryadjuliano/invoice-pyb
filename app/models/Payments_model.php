<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
| -----------------------------------------------------
| PRODUCT NAME:     SIMPLE INVOICE MANAGER
| -----------------------------------------------------
| AUTHER:           MIAN SALEEM
| -----------------------------------------------------
| EMAIL:            saleem@tecdiary.com
| -----------------------------------------------------
| COPYRIGHTS:       RESERVED BY TECDIARY IT SOLUTIONS
| -----------------------------------------------------
| WEBSITE:          http://tecdiary.com
| -----------------------------------------------------
|
| Model:            Payments
| -----------------------------------------------------
| This is payments model file.
| -----------------------------------------------------
*/

class Payments_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPayment($invoice_id, $customer_id, $amount, $note = null)
    {
        $inv   = $this->getInvoiceByID($invoice_id);
        $total = $inv->total + $inv->shipping;
        $adata = [
            'date'        => date('Y-m-d'),
            'invoice_id'  => $invoice_id,
            'customer_id' => $customer_id,
            'amount'      => $amount,
            'note'        => $note,
        ];
        if ($this->db->insert('payment', $adata)) {
            $paid = $this->getPaidAmount($invoice_id);

            if ($paid && $paid >= $total) {
                $this->db->update('sales', ['status' => 'paid', 'paid' => $paid], ['id' => $invoice_id]);
                return true;
            }
            $this->db->update('sales', ['status' => 'partial', 'paid' => $paid], ['id' => $invoice_id]);
            return true;
        }
        return false;
    }

    public function getCustomerByID($id)
    {
        $q = $this->db->get_where('customers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaidAmount($invoice_id)
    {
        $this->db->select('COALESCE(sum(amount), 0) as amount', false);
        $q = $this->db->get_where('payment', ['invoice_id' => $invoice_id]);
        if ($q->num_rows() > 0) {
            $da = $q->row();
            return $da->amount;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get('paypal');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get('skrill');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
}
