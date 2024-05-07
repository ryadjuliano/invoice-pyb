<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('cron_model');
        $this->load->model('sales_model');
        $this->load->library('tec_n2w', ['lang' => $this->Settings->selected_language], 'mywords');
    }

    public function email_details($company, $name, $email, $di, $ri)
    {
        list($user, $domain) = explode('@', $email);
        if ($domain != 'tecdiary.com') {
            $note = $di . " invoices' status has been updated to <strong>overdue</strong><br>";
            $note .= $ri . ' invoices has been created.<br>';
            $msg = '<html><body>Hello ' . $name . ' (' . $company . '),<br><br>The cron job has successfully run and<br><br>' . $note . '<br>Thank you<br>' . $this->Settings->site_name . '</body></html>';
            $this->sim->send_email($email, 'Cron job results for ' . $this->Settings->site_name, $msg, $email);
        }
    }

    public function email_invoice($sale_id)
    {
        $to = $html = $inv = null;
        $this->load->model('sales_model');
        $inv = $this->sales_model->getInvoiceByID($sale_id);

        $this->data['rows']     = $this->sales_model->getAllInvoiceItems($sale_id);
        $customer_id            = $inv->customer_id;
        $bc                     = $inv->company ?? 1;
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

        $to      = $customer->email;
        $subject = $this->lang->line('invoice_from') . ' ' . $biller->company;
        $note    = $this->lang->line('find_attached_invoice');

        $this->data['page_title'] = $this->lang->line('invoice');

        $html = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        $name = $this->lang->line('invoice') . ' ' . $this->lang->line('no') . ' ' . $inv->id . '.pdf';

        $search  = ['<div id="wrap">', '<div class="row-fluid">', '<div class="span6">', '<div class="span2">', '<div class="span10">', '<div class="span4">', '<div class="span4 offset3">', '<div class="span4 pull-left">', '<div class="span4 pull-right">'];
        $replace = ["<div style='padding:0;'>", "<div style='width: 100%;'>", "<div style='width: 48%; float: left;'>", "<div style='width: 18%; float: left;'>", "<div style='width: 78%; float: left;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>", "<div style='width: 40%; float: left;'>", "<div style='width: 40%; float: right;'>"];

        $html = str_replace($search, $replace, $html);

        $email_data  = $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        $email_data  = str_replace($search, $replace, $email_data);
        $grand_total = $inv->grand_total - $paid;
        $paypal      = $this->sales_model->getPaypalSettings();
        $skrill      = $this->sales_model->getSkrillSettings();
        $btn_code    = '<br><br><div id="payment_buttons" class="text-center margin010">';
        if ($paypal->active == '1' && $grand_total != '0.00') {
            if (trim(strtolower($customer->country)) == $biller->country) {
                $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_my / 100);
            } else {
                $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_other / 100);
            }
            $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'uploads/logos/' . $this->Settings->logo . '&amount=' . (($grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->Settings->currency_prefix . '&bn=FC-BuyNow&rm=2&return=' . site_url('clients/sales') . '&cancel_return=' . site_url('clients/sales') . '&notify_url=' . site_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/img/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
        }
        if ($skrill->active == '1' && $grand_total != '0.00') {
            if (trim(strtolower($customer->country)) == $biller->country) {
                $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_my / 100);
            } else {
                $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_other / 100);
            }
            $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'uploads/logos/' . $this->Settings->logo . '&amount=' . (($grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . site_url('clients/sales') . '&cancel_url=' . site_url('clients/sales') . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sim->formatDecimal($grand_total + $skrill_fee) . '&currency=' . $this->Settings->currency_prefix . '&status_url=' . site_url('payments/skrillipn') . '"><img src="' . base_url('assets/img/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
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

        $attachment = $this->sim->generate_pdf($html, $name, 'S');
        if ($this->sim->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
            unlink($attachment);
            return true;
        }
        return false;
    }

    // public function index()
    // {
    //     // show_404();
    // }

    public function log_cron($msg, $val = null)
    {
        $this->load->library('logs');
        return (bool) $this->logs->write('cron', $msg, $val);
    }

    public function run()
    {
        $today   = date('Y-m-d');
        $res     = '';
        
        
        
        // // $phoneNumber = $this->data['customer']->phone;
        // $grandTotal = "Rp " . number_format($inv->grand_total, 2, ",", ".");
        // $dueDate = $this->data['inv']->due_date;

        // // Parse the date string into a DateTime object
        // $dateObj = DateTime::createFromFormat('Y-m-d', $dueDate);
        
        // // Format the DateTime object into 'd/m/y' format
        // $formattedDueDate = $dateObj->format('d/m/y');
        
        // Assign the formatted due date back to your data
        // $this->data['inv']->due_date = $formattedDueDate;
        
        // $kirim = '';
        // if($this->data['customer']->cf1 === "" || $this->data['customer']->cf1 === null) {
        //     $kirim  = $this->data['inv']->shipment;
        // } else {
        //     $kirim = $this->data['customer']->cf1 ;
        // }
    
     $messages = "
Mohon di cek kembali yaaa agar aku bisa proses orderannya.
Harap mengirimkan bukti transfer dan nama rekening untuk proses konfirmasi pembayaran dan akan segera di proses bookingannya ☺️🙏
        
Notes: Pembayaran maksimal 1 jam setelah diberikan form payment ini.
        
    
*Hormat kami,*
*Pickyourballoon 🎈*";
        //$message = "Hellow PYB, please check the link https://invoice.iamindonesia.my.id/sales/pdf/$inv->id";
        $sender = '6285174059595';
        // $newPhoneNumber = str_replace("08", "628", $phoneNumber);
        $newPhoneNumber = '6281276327000';
        $keyAPI = 'kGLOeXhnyZuPYINQNbYNiSHIItX57i';
        // $baseUrl = 'https://wa.iamindonesia.site/send-message?api_key='$keyAPI'&sender='$sender'&number=6281276327000&message='$message';
        // $URLOTP = "https://wa.iamindonesia.site/send-message?api_key=$keyAPI&sender=$sender&number=6281276327000&message=$message";
        $URLOTP = "https://wa.iamindonesia.site/send-message?api_key=" . urlencode($keyAPI) . "&sender=" . urlencode($sender) . "&number=" . urlencode($newPhoneNumber) . "&message=" . urlencode($messages);
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
    
        // $sellers = $this->cron_model->getAllSellers();
        // foreach ($sellers as $seller) {
        //     $due_invoices = $this->cron_model->getAllDueInvoices($seller->id);
        //     $di           = 0;
        //     if (!empty($due_invoices)) {
        //         foreach ($due_invoices as $due_inv) {
        //             if ($due_inv->due_date && $due_inv->due_date != $due_inv->date && $due_inv->due_date < $today) {
        //                 $this->cron_model->updateInvoiceStatus($due_inv->id);
        //                 $this->email_invoice($due_inv->id);
        //                 $di++;
        //             }
        //         }
        //     }
        //     $recurring_invoices = $this->cron_model->getAllRecurringInvoices($seller->id);
        //     $ri                 = 0;
        //     if (!empty($recurring_invoices)) {
        //         foreach ($recurring_invoices as $rec_inv) {
        //             if ($rec_inv->recurring == 1) {
        //                 $rd = date('Y-m-d', strtotime('now', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 2) {
        //                 $rd = date('Y-m-d', strtotime('+7 days', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 3) {
        //                 $rd = date('Y-m-d', strtotime('+1 month', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 4) {
        //                 $rd = date('Y-m-d', strtotime('+3 months', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 5) {
        //                 $rd = date('Y-m-d', strtotime('+6 months', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 6) {
        //                 $rd = date('Y-m-d', strtotime('+1 year', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             } elseif ($rec_inv->recurring == 7) {
        //                 $rd = date('Y-m-d', strtotime('+2 years', strtotime($rec_inv->recur_date)));
        //                 if ($today >= $rd) {
        //                     $this->create_send($rec_inv->id, $rd);
        //                 }
        //             }
        //         }
        //     }
        //     $this->email_details($seller->company, $seller->name, $seller->email, $di, $ri);
        //     $res .= '<p><strong>' . $seller->company . '</strong><br>' . $di . " invoices' status has been updated to <strong>overdue</strong><br>" .
        //         $ri . ' invoices has been created.<p>';
        // }
        // echo $res;
    
    // public function schedule ()
    
    public function schedule($sale_id = null, $save_bufffer= null)
    {
      
        $dueDate = date('y-m-d');
        $newDueDate = date('Y-m-d', strtotime($dueDate . ' +15 day')); // Add 1 day to $dueDate

        // $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        // $this->data['rows']       = $this->sales_model->getAllInvoiceItems($sale_id);
        $inv = $this->sales_model->getAllSalesByDate();
        
         
        $length = count($inv);
        
        
            
        // Initialize an empty array to hold the combined data
        $combinedData = array();
        
        foreach ($inv as $sale) {
            // Access properties of $sale object
            $sale_id = $sale->id;
            $cus_id = $sale->customer_id;
            
            
             
        
            $rows = $this->sales_model->getAllInvoiceItems($sale_id);
            $cusId = $this->sales_model->getCustomerByID($cus_id);
            
     
            $combinedData[] = array(
                'sale' => $sale,
                'rows' => $rows,
                'customer' => $cusId
            );
        }
        
        // echo "<pre>";
        // print_r($combinedData);
        // exit;
        
        // Pass $combinedData to the view
         $todayS = date('Y-m-d');
        $this->data['fromdate'] = date('Y-m-d', strtotime('+1 days', strtotime($todayS)));
        $this->data['enddate'] = date('Y-m-d', strtotime('+15 days', strtotime($dueDate)));
        $this->data['combinedData'] = $combinedData;
        
        
        // $html = $this->load->view('reports/view_stock',  $this->data, true);
      
     
        // $html = $this->load->view($this->theme . 'reports/view_stock', $this->data, true);
        // $name = 'OrderPYB ' .$todayS. '.pdf';

        // if (!empty($save_bufffer)) {
        //     return $this->sim->generate_pdf($html, $name, $save_bufffer);
        // }
        // $this->sim->generate_pdf($html, $name);
        
        $this->page_construct('reports/view_stock', $this->data);
        
        // $formattedDueDate = $dateObj->format('d/m/y');
        
       // Initialize an empty string to store the combined formatted data
        // $combinedFormat = "";
        
        // foreach ($combinedData as $cus) {
         
        //     $dateKu = $cus['sale']->due_date;
            
        //     $dateObj = DateTime::createFromFormat('Y-m-d', $dateKu);
    
        //     $formattedDueDate = $dateObj->format('d/m/y');
            
            
            
        //     $kirimShipment = "";
        //     if($cus['sale']->shipment === "" || $cus['sale']->shipment === null ) {
        //         $kirimShipment = $cus['customer']->cf1;
        //     } else {
        //         $kirimShipment = $cus['sale']->shipment;
        //     }
            
        //     $format = "Orderan untuk tanggal " . $formattedDueDate . " Untuk Customer *" . $cus['sale']->customer_name . "*\n" . " Di Kirim :  *" . $kirimShipment . "*\n" ;
            
        //     // Counter variable to keep track of the number
        //     $counter = 1;
        
        //     // Loop through each item in $cus['rows']
        //     foreach ($cus['rows'] as $items) {
        //         // Concatenate each product name with its corresponding number to the existing $format string
        //         // $format .= $counter . ". " . $items->product_name . "\n";
        //         if($items->details === "" || $items->details === null) {
        //             $StringTema = "";
        //         } else {
        //              $StringTema = " Dengan Tema *" . $items->details . "*\n";
        //         }
        //         $format .= $StringTema ."*Details : *\n". $counter . ". " . $items->product_name . "\n";
                
        //         // Increment the counter
        //         $counter++;
        //     }
            
        //     // Concatenate the formatted string for this set of data with the combined formatted data
        //     $combinedFormat .= $format . "\n";
        // }
        
        // Output the combined formatted string
        // echo "<pre />";
        // print_r($combinedFormat);
        // exit();
    
        // $name = 'Stock ' .$dueDate . '.pdf';

        // // if (!empty($save_bufffer)) {
        // //         return $this->sim->generate_pdf($combinedFormat, $name, $save_bufffer);
        // //     }
        // // $this->sim->generate_pdf($combinedFormat, $name);
        // // $this->data, true
        //  $html = $this->load->view($this->theme . 'reports/view_stock');
        // //  echo $html;
        // // //  print_r($html);
        // //  exit;
        //  $this->sim->generate_pdf($html, $name);
        //  $this->page_construct('reports/view_stock', $this->data);
        
        
        // if ($view) {
        //     echo $this->load->view($this->theme . 'sales/view_invoice', $this->data, true);
        //     exit();
        // }
        
        // $datae['customer']
    
        // if (!empty($save_bufffer)) {
        //     return $this->sim->generate_pdf($html, $name, $save_bufffer);
        // }
        // $this->sim->generate_pdf($html, $name, $save_bufffer);
        //  echo $this->load->view($this->theme . 'reports/view_stock',true);
            // exit();
         
        //  exit();
        // Send WhatsApp message with the combined formatted data
        // $sender = '6285174059595';
        // $newPhoneNumber = '6285162636488';
        // $keyAPI = 'kGLOeXhnyZuPYINQNbYNiSHIItX57i';
        // $URLOTP = "https://wa.iamindonesia.site/send-message?api_key=" . urlencode($keyAPI) . "&sender=" . urlencode($sender) . "&number=" . urlencode($newPhoneNumber) . "&message=" . urlencode($combinedFormat);
        // $curl = curl_init();
            
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $URLOTP,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'GET',
        // ));
            
        // $response = curl_exec($curl);
        // curl_close($curl);
        
        // if($response === false) {
        //     echo "cURL Error: " . curl_error($curl);
        // } else {
        //     // Output the response
        //     echo $response;
        //     // redirect('sales');
        // }

        
        

    }
    

    private function create_send($id, $rd)
    {
        if ($iid = $this->cron_model->createInvoice($id, $rd)) {
            $this->email_invoice($iid);
        }
    }
}
