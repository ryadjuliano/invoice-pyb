<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sales extends MY_Controller
{
 
    public function __construct()
    {
        parent::__construct();

        // if (!$this->sim->logged_in()) {
        //     redirect('auth/login');
        // }
        // if ($this->sim->in_group('customer')) {
        //     $this->session->set_flashdata('error', $this->lang->line('access_denied'));
        //     redirect('clients');
        // }
        $this->load->library('form_validation');
        $this->load->model('sales_model');
        $this->load->library('tec_n2w', ['lang' => $this->Settings->selected_language], 'mywords');
    }

    /* -------------------------------------------------------------------- */

    public function add($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($inv = $this->sales_model->getQuoteByID($id)) {
            $this->sim->view_rights($inv->user_id);
        }
        // getAllSales
        $refrence = $this->sales_model->getLastSalesReferenceNo();
        // // $refrence = $salesRef->reference_no;
        // print_r($refrence);
        // exit;

        $this->form_validation->set_rules('status', $this->lang->line('status'), 'required');
        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required|is_unique[sales.reference_no]');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');
        if ($this->input->post('customer') == 'new') {
            $this->form_validation->set_rules('name', $this->lang->line('customer') . ' ' . $this->lang->line('name'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('customer') . ' ' . $this->lang->line('email_address'), 'required|trim|valid_email|is_unique[customers.email]');
            $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');
        }

        if ($this->form_validation->run() == true) {
            $form          = $this->sales_model->process_form();
            $customer_data = $form['customer_data'];
            $products      = $form['products'];
            $data          = $form['data'];
            unset($data['expiry_date']);

            // echo '<pre />'; print_r($form); die();
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $customer_data)) {
            $this->session->set_flashdata('message', $this->lang->line('sale_added'));
            redirect('sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($id) {
                $inv->due_date              = '';
                $inv->recurring             = '';
                $this->data['inv_products'] = $this->sales_model->getAllQuoteItems($id);
            }
            $this->data['inv']          = $id ? $inv : false;
            $this->data['q']            = false;
            $this->data['default_note'] = $this->sales_model->getDefaultNote();
            $this->data['customers']    = $this->sales_model->getAllCustomers();
            $this->data['tax_rates']    = $this->sales_model->getAllTaxRates();
            $this->data['companies']    = $this->sales_model->getAllCompanies();
            $this->data['page_title']   = $this->lang->line('add_sale');
            $this->data['refrenceByNo'] = $refrence;
            $this->page_construct('sales/add', $this->data);
        }
    }

    public function add_payment()
    {
        if ($this->input->post('invoice_id')) {
            $invoice_id = $this->input->post('invoice_id');
        } else {
            $invoice_id = null;
            die();
        }
        if ($this->input->post('customer_id')) {
            $customer_id = $this->input->post('customer_id');
        } else {
            $customer_id = null;
            die();
        }
        if ($this->input->post('note')) {
            $note = $this->input->post('note');
        } else {
            $note = null;
        }
        if ($this->input->post('amount')) {
            $amount = $this->input->post('amount');
        } else {
            $amount = null;
            die();
        }
        if ($invoice_id && $customer_id && $amount) {
            $date = $this->input->post('date') ? $this->sim->fsd($this->input->post('date')) : date('Y-m-d');

            if ($this->sales_model->addPayment($invoice_id, $customer_id, $amount, $note, $date)) {
                $this->session->set_flashdata('message', $this->lang->line('amount_added'));
                redirect('sales');
            }
        }

        return false;
    }

    //Add new quote

    public function add_quote()
    {
        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');
        if ($this->input->post('customer') == 'new') {
            $this->form_validation->set_rules('name', $this->lang->line('customer') . ' ' . $this->lang->line('name'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('customer') . ' ' . $this->lang->line('email_address'), 'required|trim|valid_email|is_unique[customers.email]');
            $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');
        }

        if ($this->form_validation->run() == true) {
            $form          = $this->sales_model->process_form();
            $customer_data = $form['customer_data'];
            $products      = $form['products'];
            $data          = $form['data'];
            unset($data['due_date'], $data['recurring']);

            // echo '<pre />'; var_dump($data); var_dump($products); die();
        }

        if ($this->form_validation->run() == true && $this->sales_model->addQuote($data, $products, $customer_data)) {
            $this->session->set_flashdata('message', $this->lang->line('quote_added'));
            redirect('sales/quotes');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']          = false;
            $this->data['q']            = true;
            $this->data['default_note'] = $this->sales_model->getDefaultNote('default_quote');
            $this->data['customers']    = $this->sales_model->getAllCustomers();
            $this->data['tax_rates']    = $this->sales_model->getAllTaxRates();
            $this->data['companies']    = $this->sales_model->getAllCompanies();
            $this->data['page_title']   = $this->lang->line('new_quote');
            $this->page_construct('sales/add_quote', $this->data);
        }
    }

    public function add_sale_payment($sale_id)
    {
        $sale = $this->sales_model->getInvoiceByID($sale_id);
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'date'        => $this->sim->fsd($this->input->post('date')),
                'amount'      => $this->input->post('amount'),
                'note'        => $this->input->post('note'),
                'invoice_id'  => $sale->id,
                'customer_id' => $sale->customer_id,
                'user'        => $this->session->userdata('user_id')
            ];
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSalePayment($data, $sale)) {
            $this->session->set_flashdata('message', $this->lang->line('amount_added'));
            redirect('sales');
        } else {
            $this->data['sale']       = $sale;
            $this->data['page_title'] = $this->lang->line('add_payment');
            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    /*-------------------------------*/
    public function delete($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            redirect('home');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if (!$this->sim->in_group('admin')) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('sales');
        }

        if ($this->sales_model->deleteInvoice($id)) {
            $this->session->set_flashdata('message', $this->lang->line('invoice_deleted'));
            redirect('sales');
        }
    }

    public function delete_payment($id = null)
    {
        if (!$this->sim->in_group('admin')) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('sales');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->sales_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_quote($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            redirect('home');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if (!$this->sim->in_group('admin')) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('sales');
        }

        if ($this->sales_model->deleteQuote($id)) {
            $this->session->set_flashdata('message', $this->lang->line('quote_deleted'));
            redirect('sales/quotes');
        }
    }

    /* --------------------------------------------------- */
    //Edit sale

    public function edit($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->sim->view_rights($inv->user_id);

        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('status', $this->lang->line('status'), 'required');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');
        if ($this->input->post('customer') == 'new') {
            $this->form_validation->set_rules('name', $this->lang->line('customer') . ' ' . $this->lang->line('name'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('customer') . ' ' . $this->lang->line('email_address'), 'required|trim|valid_email|is_unique[customers.email]');
            $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');
        }

        if ($this->form_validation->run() == true) {
            $form          = $this->sales_model->process_form();
            $customer_data = $form['customer_data'];
            $products      = $form['products'];
            $data          = $form['data'];
            unset($data['expiry_date'], $data['user'], $data['user_id']);

            // $this->sim->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products, $customer_data)) {
            $this->session->set_flashdata('message', $this->lang->line('sale_updated'));
            redirect('sales');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['customers']    = $this->sales_model->getAllCustomers();
            $this->data['tax_rates']    = $this->sales_model->getAllTaxRates();
            $this->data['inv']          = $inv;
            $this->data['q']            = false;
            $this->data['id']           = $id;
            $this->data['inv_products'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['page_title']   = $this->lang->line('update_sale');
            $this->data['companies']    = $this->sales_model->getAllCompanies();
            $this->page_construct('sales/edit', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'date'   => $this->sim->fsd($this->input->post('date')),
                'amount' => $this->input->post('amount'),
                'note'   => $this->input->post('note'),
            ];
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('payment_updated'));
            redirect('sales');
        } else {
            $this->data['payment']    = $this->sales_model->getPaymentByID($id);
            $this->data['page_title'] = $this->lang->line('edit_payment');
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    //Edit quote

    public function edit_quote($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getQuoteByID($id);
        $this->sim->view_rights($inv->user_id);

        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');
        if ($this->input->post('customer') == 'new') {
            $this->form_validation->set_rules('name', $this->lang->line('customer') . ' ' . $this->lang->line('name'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('customer') . ' ' . $this->lang->line('email_address'), 'required|trim|valid_email|is_unique[customers.email]');
            $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');
        }

        if ($this->form_validation->run() == true) {
            $form          = $this->sales_model->process_form();
            $customer_data = $form['customer_data'];
            $products      = $form['products'];
            $data          = $form['data'];
            unset($data['due_date'], $data['recurring'], $data['user'], $data['user_id']);

            // echo '<pre />'; var_dump($data); var_dump($products); die();
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateQuote($id, $data, $products, $customer_data)) {
            $this->session->set_flashdata('message', $this->lang->line('quote_updated'));
            redirect('sales/quotes');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['customers']    = $this->sales_model->getAllCustomers();
            $this->data['tax_rates']    = $this->sales_model->getAllTaxRates();
            $this->data['inv']          = $this->sales_model->getQuoteByID($id);
            $this->data['q']            = true;
            $this->data['inv_products'] = $this->sales_model->getAllQuoteItems($id);
            $this->data['id']           = $id;
            $meta['page_title']         = $this->lang->line('update_quote');
            $this->data['page_title']   = $this->lang->line('update_quote');
            $this->data['companies']    = $this->sales_model->getAllCompanies();
            $this->page_construct('sales/edit_quote', $this->data);
        }
    }

    /* ---------------------------------------- */

    public function email($sale_id = null, $to = null, $from_name = null, $from = null, $subject = null, $note = null, $cc = null, $bcc = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']     = $this->sales_model->getAllInvoiceItems($sale_id);
        $inv                    = $this->sales_model->getInvoiceByID($sale_id);
        $customer_id            = $inv->customer_id;
        $bc                     = $inv->company_id ? $inv->company_id : 1;
        $biller                 = $this->sales_model->getCompanyByID($bc);
        $customer               = $this->sales_model->getCustomerByID($customer_id);
        $payment                = $this->sales_model->getPaymentBySaleID($sale_id);
        $paid                   = $this->sales_model->getPaidAmount($sale_id);
        $this->data['inv']      = $inv;
        $this->data['sid']      = $sale_id;
        $this->data['biller']   = $biller;
        $this->data['customer'] = $customer;
        $this->data['payment']  = $payment;
        $this->data['paid']     = $paid;
        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$subject) {
            $subject = $this->lang->line('invoice_from') . ' ' . $this->data['biller']->company;
        }
        if (!$note) {
            $note = $this->lang->line('find_attached_invoice');
        }
        $this->data['page_title'] = $this->lang->line('invoice');
        $html                     = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        $name                     = 'Invoice ' . $inv->id . '.pdf';

        $search  = ['<div id="wrap">', '<div class="row-fluid">', '<div class="span6">', '<div class="span2">', '<div class="span10">', '<div class="span4">', '<div class="span4 offset3">', '<div class="span4 pull-left">', '<div class="span4 pull-right">'];
        $replace = ["<div style='padding:0;'>", "<div style='width: 100%;'>", "<div style='width: 48%; float: left;'>", "<div style='width: 18%; float: left;'>", "<div style='width: 78%; float: left;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>"];

        $html = str_replace($search, $replace, $html);

        $email_data  = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        $email_data  = str_replace($search, $replace, $email_data);
        $grand_total = $inv->grand_total - $paid;
        $paypal      = $this->sales_model->getPaypalSettings();
        $skrill      = $this->sales_model->getSkrillSettings();
        $stripe      = $this->sales_model->getStripeSettings();
        $btn_code    = '<br><br><div id="payment_buttons" class="text-center margin010" style="display:flex;align-items:center;">';
        if ($stripe->active == '1' && $grand_total != '0.00') {
            $btn_code .= ' <a style="background:#5469d4;border-radius:5px;padding:1rem 1.5rem;font-size:1rem;font-weight:bold;color:#fff;text-decoration:none;margin:0 1rem 0 0;" href="' . site_url('payments/stripe/' . $inv->id) . '/' . base64_encode($inv->id) . '">' . lang('pay_with_cc') . '</a>';
        }
        if ($paypal->active == '1' && $grand_total != '0.00') {
            if (trim(strtolower($customer->country)) == $biller->country) {
                $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_my / 100);
            } else {
                $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_other / 100);
            }
            $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'uploads/' . $this->Settings->logo . '&amount=' . (($grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->Settings->currency_prefix . '&bn=FC-BuyNow&rm=2&return=' . site_url('clients/sales') . '&cancel_return=' . site_url('clients/sales') . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('uploads/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
        }
        if ($skrill->active == '1' && $grand_total != '0.00') {
            if (trim(strtolower($customer->country)) == $biller->country) {
                $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_my / 100);
            } else {
                $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_other / 100);
            }
            $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'uploads/' . $this->Settings->logo . '&amount=' . (($grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('clients/sales') . '&cancel_url=' . site_url('clients/sales') . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sim->formatDecimal($grand_total + $skrill_fee) . '&currency=' . $this->Settings->currency_prefix . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('uploads/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
        }

        $btn_code .= '<div class="clearfix"></div></div>';

        $note = $note . $btn_code;

        if ($this->Settings->email_html) {
            if ($note) {
                $message = $note . '<br /><hr>' . $email_data;
            } else {
                $message = $email_data;
            }
        } else {
            $message = $note;
        }

        $result     = false;
        $attachment = $this->pdf($sale_id, 'S');
        try {
            if ($this->sim->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                $result = true;
            }
        } catch (\Exception $e) {
            // $this->session->set_falshdata('error', $e->getMessage());
            log_message('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        unlink($attachment);
        return $result;
    }

    public function emailQ($id, $to, $from_name, $from, $subject, $note, $cc = null, $bcc = null)
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']       = $this->sales_model->getAllQuoteItems($id);
        $inv                      = $this->sales_model->getQuoteByID($id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['inv']        = $inv;
        $this->data['page_title'] = $this->lang->line('quote');
        $html1                    = $this->load->view($this->theme . 'sales/view_quote', $this->data, true);
        $name                     = 'Quotation ' . $inv->id . '.pdf';

        $search  = ['<div class="row-fluid">', '<div class="span6">', '<div class="span2">', '<div class="span10">', '<div class="span4">', '<div class="span4 offset3">', '<div class="span4 pull-left">', '<div class="span4 pull-right">'];
        $replace = ["<div style='width: 100%;'>", "<div style='width: 48%; float: left;'>", "<div style='width: 18%; float: left;'>", "<div style='width: 78%; float: left;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>"];

        $html1 = str_replace($search, $replace, $html1);
        $html  = $this->load->view($this->theme . 'sales/view_quote', $this->data, true);
        $html  = str_replace($search, $replace, $html);

        if ($this->Settings->email_html) {
            if ($note) {
                $message = $note . '<br /><hr>' . $html;
            } else {
                $message = $html;
            }
        } else {
            $message = $note;
        }

        $result     = false;
        $attachment = $this->pdf_quote($id, 'S');
        try {
            if ($this->sim->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                $result = true;
            }
        } catch (\Exception $e) {
            // $this->session->set_falshdata('error', $e->getMessage());
            log_message('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        unlink($attachment);
        return $result;
    }

    public function getCE()
    {
        if ($this->input->get('cid')) {
            $cid = $this->input->get('cid');
        } else {
            $cid = null;
            die();
        }
        if ($this->input->get('bid')) {
            $bid = $this->input->get('bid');
        } else {
            $bid = null;
            die();
        }
        $cus = $this->sales_model->getCustomerByID($cid);
        $com = $this->sales_model->getCompanyByID($bid);
        echo json_encode(['ce' => $cus->email, 'com' => ($com->company && $com->company != '-' ? $com->company : $com->name)]);
    }

    public function getdatatableajax()
    {
        if ($this->Settings->restrict_sales && !$this->sim->in_group('admin')) {
            $check = true;
        } else {
            $check = null;
        }
        $user_id = $this->session->userdata('user_id');
          
        $message = "Hellow PYB, please check the link https://invoice.iamindonesia.my.id/sales/pdf/$1/<?php echo $customer_id;?>";
        
        // $add_payment_link = anchor('#', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="add_payment" id="$1" data-customer="$2" data-company="$3"');
        $add_payment_link = anchor('sales/add_sale_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="ajax-modal"');
        $detail_link      = anchor('sales/view_invoice/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_invoice'), 'data-toggle="ajax-modal"');
        $email_link      = anchor('sales/view_sendMessage/$1', '<i class="fa fa-file-text-o"></i> ' .'SEND WA');
        
        // view_sendMessage
        $payments_link    = anchor('sales/view_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="ajax-modal"');
        // $email_link       = anchor('', '<i class="fa fa-envelope"></i> ' . 'Send Whatsapp', 'class="" id="$1" data-customer="$2" data-company="$3"');
        // $email_link      = "<a href='https://wa.iamindonesia.site/send-message?api_key=PbQAIIB21DKdbQ5fiiQVpqs9rqlZXj&sender=6285174059595&number=6281276327000&message=.$message.' onClick=\"return confirm('" . lang('alert_x_invoice') . "')\"><i class=\"fa fa-trash-o\"></i> " .'Send WHATS'. '</a>';
        //  $email_link      = "<a href='" . site_url('sales/view_sendMessage/$1') . "' onClick=\"return confirm('" . lang('alert_x_invoice') . "')\"><i class=\"fa fa-trash-o\"></i> " .' SEND WA'. '</a>';
        
        $edit_link        = anchor('sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_invoice'));
        $pdf_link         = anchor('sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link      = "<a href='" . site_url('sales/delete/$1') . "' onClick=\"return confirm('" . lang('alert_x_invoice') . "')\"><i class=\"fa fa-trash-o\"></i> " . lang('delete_invoice') . '</a>';
      
        $WaGW = "https://wa.iamindonesia.site/send-message?api_key=PbQAIIB21DKdbQ5fiiQVpqs9rqlZXj&sender=6285174059595&number=6281276327000&message=.$message.";
        
        $action           = '<div class="text-center row-menu"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id');
        } else {
            $customer_id = null;
        }
        $this->load->library('datatables');
        //(CASE WHEN users.first_name is null THEN sales.user ELSE CONCAT(users.first_name, ' ', users.last_name) END) as user
        $this->datatables
            ->select("{$this->db->dbprefix('sales')}.id as sid, date as date, ballon_status as ballon_status , company_name, reference_no, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, customer_name, grand_total, paid, (grand_total)-COALESCE(paid, 0) as balance,phone as telephone, due_date, status as status, recurring,{$this->db->dbprefix('sales')}.customer_id as cid, company_id as bid", false)
            ->from('sales')
            ->join('users', 'users.id=sales.user', 'customers','customers.phone=users.phone' ,'left')
            ->group_by('sales.id');
        if ($customer_id) {
            $this->datatables->where('sales.customer_id', $customer_id);
        }
        if ($check) {
            $this->datatables->where('sales.user', $user_id);
        }
        $this->datatables->edit_column('status', '$1-$2', 'status, sid')
            ->add_column('Actions', $action, 'sid, cid, bid')

            ->unset_column('cid')
            ->unset_column('bid');
    
        echo $this->datatables->generate();
    }

    public function getquotes()
    {
        if ($this->Settings->restrict_sales && !$this->sim->in_group('admin')) {
            $check = true;
        } else {
            $check = null;
        }

        $detail_link  = anchor('sales/view_quote/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_quote'), 'data-toggle="ajax-modal"');
        $convert_link = anchor('sales/add/$1', '<i class="fa fa-share"></i> ' . lang('quote_to_invoice'));
        $email_link   = anchor('#', '<i class="fa fa-envelope"></i> ' . lang('email_quote'), 'class="email_inv" id="$1" data-customer="$2" data-company="$3"');
        $edit_link    = anchor('sales/edit_quote/$1', '<i class="fa fa-edit"></i> ' . lang('edit_quote'));
        $pdf_link     = anchor('sales/pdf_quote/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link  = "<a href='" . site_url('sales/delete_quote/$1') . "' onClick=\"return confirm('" . lang('alert_x_quote') . "')\"><i class=\"fa fa-trash-o\"></i> " . lang('delete_quote') . '</a>';
        $action       = '<div class="text-center row-menu"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $convert_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('quotes')}.id as id, date, company_name, reference_no, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, customer_name, total, total_tax, COALESCE(shipping, 0) as shipping, COALESCE(total_discount, 0) as discount, grand_total, status, {$this->db->dbprefix('quotes')}.customer_id as cid, company_id as bid, expiry_date", false)
            ->from('quotes')
            ->join('users', 'users.id=quotes.user', 'left')
            ->group_by('quotes.id');
        if ($check) {
            $this->datatables->where('user', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id, cid, bid')
            ->unset_column('cid')
            ->unset_column('bid');

        echo $this->datatables->generate();
        
    }

    /* -------------------------------------------------------- */
    //index or inventories page

    public function index()
    {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        if ($this->input->get('customer_id')) {
            $this->data['customer_id'] = $this->input->get('customer_id');
        } else {
            $this->data['customer_id'] = null;
        }
        $user                     = $this->site->getUser();
        $this->data['from_name']  = $user->first_name . ' ' . $user->last_name;
        $this->data['from_email'] = $user->email;

        $this->data['page_title'] = $this->lang->line('invoices');
        $this->page_construct('sales/index', $this->data);
    }

    public function notes()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->sales_model->getNotes($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->description];
            }
            $this->sim->send_json($pr);
        }
        echo false;
    }

    public function payment_note($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->invoice_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = $this->lang->line('payment_note');
        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    /* ------------------------------------------------------------ */
    //generate pdf and force to download

    public function pdf($sale_id = null, $save_bufffer = null, $view = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }

        // $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $inv                      = $this->sales_model->getInvoiceByID($sale_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['payment']    = $this->sales_model->getPaymentBySaleID($sale_id);
        $this->data['paid']       = $this->sales_model->getPaidAmount($sale_id);
        $this->data['rows']       = $this->sales_model->getAllInvoiceItems($inv->id);
        $this->data['created_by'] = $this->site->getUser($inv->user_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $sale_id;
        $this->data['page_title'] = $this->lang->line('invoice');
        if ($view) {
            echo $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
            exit();
        }
        $html = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        $name = 'Invoice ' . $inv->id . '.pdf';

        if (!empty($save_bufffer)) {
            return $this->sim->generate_pdf($html, $name, $save_bufffer);
        }
        $this->sim->generate_pdf($html, $name);
    }

    public function pdf_quote($quote_id = null, $save_bufffer = null, $view = null)
    {
        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

        $inv                      = $this->sales_model->getQuoteByID($quote_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['rows']       = $this->sales_model->getAllQuoteItems($inv->id);
        $this->data['inv']        = $inv;
        $this->data['page_title'] = $this->lang->line('quote');
        if ($view) {
            echo $this->load->view($this->theme . 'sales/view_quote', $this->data, true);
            exit();
        }
        $html = $this->load->view($this->theme . 'sales/view_quote', $this->data, true);
        $name = 'Quotation ' . $inv->id . '.pdf';

        if (!empty($save_bufffer)) {
            return $this->sim->generate_pdf($html, $name, $save_bufffer);
        }
        $this->sim->generate_pdf($html, $name);
    }

    public function pr_details()
    {
        if ($this->input->get('name')) {
            $name = $this->input->get('name', true);
        }

        $product = [];
        if ($item = $this->sales_model->getProductByName($name)) {
            $product = ['price' => $item->price, 'tax_rate' => $item->tax_rate, 'details' => $item->details];
        }

        echo json_encode($product);
    }

    public function quotes()
    {
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $user                     = $this->site->getUser();
        $this->data['from_name']  = $user->first_name . ' ' . $user->last_name;
        $this->data['from_email'] = $user->email;
        $this->data['page_title'] = $this->lang->line('quotes');
        $this->page_construct('sales/quotes', $this->data);
    }

    public function roundnum($num, $nearest = 0.05)
    {
        return round($num / $nearest) * $nearest;
    }

    public function send_email()
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        } else {
            $id = null;
            die();
        }
        if ($this->input->post('to')) {
            $to = $this->input->post('to');
        } else {
            $to = null;
            die();
        }
        if ($this->input->post('subject')) {
            $subject = $this->input->post('subject');
        } else {
            $subject = null;
        }
        if ($this->input->post('note')) {
            $message = $this->input->post('note');
        } else {
            $message = null;
        }
        if ($this->input->post('cc')) {
            $cc = $this->input->post('cc');
        } else {
            $cc = null;
        }
        if ($this->input->post('bcc')) {
            $bcc = $this->input->post('bcc');
        } else {
            $bcc = null;
        }

        $user      = $this->site->getUser();
        $from_name = $user->first_name . ' ' . $user->last_name;
        $from      = $user->email;

        $result = false;
        try {
            if ($this->email($id, $to, $from_name, $from, $subject, $message, $cc, $bcc)) {
                $result = lang('email_sent');
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        $this->sim->send_json(strip_tags($result));
    }

    public function send_quote()
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        } else {
            $id = null;
            die();
        }
        if ($this->input->post('to')) {
            $to = $this->input->post('to');
        } else {
            $to = null;
            die();
        }
        if ($this->input->post('subject')) {
            $subject = $this->input->post('subject');
        } else {
            $subject = null;
        }
        if ($this->input->post('note')) {
            $message = $this->input->post('note');
        } else {
            $message = null;
        }
        if ($this->input->post('cc')) {
            $cc = $this->input->post('cc');
        } else {
            $cc = null;
        }
        if ($this->input->post('bcc')) {
            $bcc = $this->input->post('bcc');
        } else {
            $bcc = null;
        }

        $user      = $this->site->getUser();
        $from_name = $user->first_name . ' ' . $user->last_name;
        $from      = $user->email;

        $result = false;
        try {
            if ($this->emailQ($id, $to, $from_name, $from, $subject, $message, $cc, $bcc)) {
                $result = lang('email_sent');
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        $this->sim->send_json(strip_tags($result));
    }

    /*-----------------------------------------------------------*/
    public function update_status()
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        } else {
            $id = null;
            die();
        }
        if ($this->input->post('status')) {
            $status = $this->input->post('status');
        } else {
            $status = null;
            die();
        }
        if ($id && $status) {
            $inv = $this->sales_model->getInvoiceByID($id);
            $this->sim->view_rights($inv->user_id);
            if ($this->sales_model->updateStatus($id, $status)) {
                $this->session->set_flashdata('message', $this->lang->line('status_updated'));
                redirect('sales');
            }
        }
        return false;
    }
    
     public function update_status_ballon()
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        } else {
            $id = null;
            die();
        }
        if ($this->input->post('status')) {
            $status = $this->input->post('status');
        } else {
            $status = null;
            die();
        }
        
        // echo $id;
        if ($id && $status) {
            $inv = $this->sales_model->getInvoiceByID($id);
            $this->sim->view_rights($inv->user_id);
            if ($this->sales_model->updateStatusBallon($id, $status)) {
                $this->session->set_flashdata('message', $this->lang->line('status_updated'));
                redirect('sales');
            }
        }
        return false;
    }


    /* ------------------------------------------------------------------ */

    public function view_invoice($sale_id = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']       = $this->sales_model->getAllInvoiceItems($sale_id);
        $inv                      = $this->sales_model->getInvoiceByID($sale_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['payment']    = $this->sales_model->getPaymentBySaleID($sale_id);
        $this->data['paid']       = $this->sales_model->getPaidAmount($sale_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $sale_id;
        $this->data['spay']       = true;
        $this->data['stripe']     = $this->sales_model->getStripeSettings();
        $this->data['created_by'] = $this->site->getUser($inv->user_id);
        $this->data['page_title'] = $this->lang->line('invoice');
        $this->load->view($this->theme . 'sales/view_invoice_modal', $this->data);
    }

    public function view_payments($sale_id = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']       = $this->sales_model->getAllInvoiceItems($sale_id);
        $inv                      = $this->sales_model->getInvoiceByID($sale_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['payment']    = $this->sales_model->getPaymentBySaleID($sale_id);
        $this->data['paid']       = $this->sales_model->getPaidAmount($sale_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $sale_id;
        $this->data['page_title'] = $this->lang->line('payments');
        $this->load->view($this->theme . 'sales/view_payments', $this->data);
    }

    public function view_quote($quote_id = null)
    {
        if ($this->input->get('id')) {
            $quote_id = $this->input->get('id');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']       = $this->sales_model->getAllQuoteItems($quote_id);
        $inv                      = $this->sales_model->getQuoteByID($quote_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $quote_id;
        $this->data['created_by'] = $this->site->getUser($inv->user_id);
        $this->data['page_title'] = $this->lang->line('quote');
        $this->load->view($this->theme . 'sales/view_quote_modal', $this->data);
    }
    
    
    public function view_sendMessage($sale_id = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['rows']       = $this->sales_model->getAllInvoiceItems($sale_id);
        $inv                      = $this->sales_model->getInvoiceByID($sale_id);
        $customer_id              = $inv->customer_id;
        $bc                       = $inv->company_id ? $inv->company_id : 1;
        $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
        $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
        $this->data['payment']    = $this->sales_model->getPaymentBySaleID($sale_id);
        $this->data['paid']       = $this->sales_model->getPaidAmount($sale_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $sale_id;
        $this->data['spay']       = true;
        $this->data['stripe']     = $this->sales_model->getStripeSettings();
        $this->data['created_by'] = $this->site->getUser($inv->user_id);
        $this->data['page_title'] = $this->lang->line('invoice');
        
        $phoneNumber = $this->data['customer']->phone;
        $grandTotal = "Rp " . number_format($inv->grand_total, 2, ",", ".");
        $dueDate = $this->data['inv']->due_date;

        // Parse the date string into a DateTime object
        $dateObj = DateTime::createFromFormat('Y-m-d', $dueDate);
        
        // Format the DateTime object into 'd/m/y' format
        $formattedDueDate = $dateObj->format('d/m/y');
        
        // Assign the formatted due date back to your data
        // $this->data['inv']->due_date = $formattedDueDate;
        
        $kirim = '';
        if($this->data['customer']->cf1 === "" || $this->data['customer']->cf1 === null) {
            $kirim  = $this->data['inv']->shipment;
        } else {
            $kirim = $this->data['customer']->cf1 ;
        }
        // echo "<pre />";
    //   print_r($kirim);
    //   print_r($this->data['inv']);
    //   exit();
//         $messages = "
//         Halo ka, berikut aku kirimin invoice nya. Total nya : $grandTotal
// invoice terlampir : https://invoice.iamindonesia.my.id/sales/pdf/$inv->id

// Mohon di cek kembali yaaa agar aku bisa proses orderannya.
// Harap mengirimkan bukti transfer dan nama rekening untuk proses konfirmasi pembayaran dan akan segera di proses bookingannya ☺️🙏
        
// Notes: Pembayaran maksimal 1 jam setelah diberikan form payment ini.
        
    
// Hormat kami,
// Pickyourballoon 🎈";
    
     $messages = "
Halo ka, berikut aku kirimin invoice nya. *Total nya pembayaran sebesar: $grandTotal*
*invoice terlampir : https://invoice.iamindonesia.my.id/sales/pdf/$inv->id*

*Nomor Rekening* : *5495155363*
*Bank* : *BCA*
*Atas Nama* : *Reny Puteri* 
*Tanggal pengiriman* : *$formattedDueDate*
*Jam pengiriman* : *$kirim*

Mohon di cek kembali yaaa agar aku bisa proses orderannya.
Harap mengirimkan bukti transfer dan nama rekening untuk proses konfirmasi pembayaran dan akan segera di proses bookingannya ☺️🙏
        
Notes: Pembayaran maksimal 1 jam setelah diberikan form payment ini.
        
    
*Hormat kami,*
*Pickyourballoon 🎈*";
        $message = "Hellow PYB, please check the link https://invoice.iamindonesia.my.id/sales/pdf/$inv->id";
        $sender = '6285174059595';
         $newPhoneNumber = preg_replace('/^08/', '628', $phoneNumber);
    //   $newPhoneNumber = '6285162636488';
        
        // print_r($newPhoneNumber);
        // exit;0817850023
        // $newPhoneNumber = '6281276327000';
        $keyAPI = 'DYVIRD7NGg5KnQ9Rwh5XDYkJWVoOga';
        // $baseUrl = 'https://wa.iamindonesia.site/send-message?api_key='$keyAPI'&sender='$sender'&number=6281276327000&message='$message';
        // $URLOTP = "https://wa.iamindonesia.site/send-message?api_key=$keyAPI&sender=$sender&number=6281276327000&message=$message";
        $URLOTP = "https://gw.iamindonesia.cloud/send-message?api_key=" . urlencode($keyAPI) . "&sender=" . urlencode($sender) . "&number=" . urlencode($newPhoneNumber) . "&message=" . urlencode($messages);
       $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => $URLOTP,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        if($response === false) {
            echo "cURL Error: " . curl_error($curl);
        } else {
            // Output the response
            echo $response;
            // redirect('sales');
        }
        // echo $response;

        
        // $this->load->view($this->theme . 'sales/view_invoice_modal', $this->data);
    }
    
    // public function view_sendMessage($quote_id = null)
    // {
    //     if ($this->input->get('id')) {
    //         $quote_id = $this->input->get('id');
    //     }
    //     $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //     $this->data['rows']       = $this->sales_model->getAllQuoteItems($quote_id);
    //     $inv                      = $this->sales_model->getQuoteByID($quote_id);
    //     $customer_id              = $inv->customer_id;
    //     $bc                       = $inv->company_id ? $inv->company_id : 1;
    //     $this->data['biller']     = $this->sales_model->getCompanyByID($bc);
    //     $this->data['customer']   = $this->sales_model->getCustomerByID($customer_id);
    //     $this->data['inv']        = $inv;
    //     $this->data['sid']        = $quote_id;
    //     echo "<pre />";
    //     print_r($customer_id);
    //     exit;
    //     // $this->data['created_by'] = $this->site->getUser($inv->user_id);
    //     // $this->data['page_title'] = $this->lang->line('quote');
    //     // $this->load->view($this->theme . 'sales/view_quote_modal', $this->data);
    // }
}
