<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
CUSTOM BY iamindonesia
| -----------------------------------------------------
| This is inventories module's model file.
| -----------------------------------------------------
*/

class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPayment($invoice_id, $customer_id, $amount, $note = null, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $inv   = $this->getInvoiceByID($invoice_id);
        $total = $inv->total + $inv->shipping;
        $adata = [
            'date'        => $date,
            'invoice_id'  => $invoice_id,
            'customer_id' => $customer_id,
            'amount'      => $amount,
            'note'        => $note,
            'user'        => $this->session->userdata('user_id')
        ];
        if ($this->db->insert('payment', $adata)) {
            $paid = $this->getPaidAmount($invoice_id);

            if ($paid >= $total) {
                $this->db->update('sales', ['status' => 'paid', 'paid' => $paid], ['id' => $invoice_id]);
                return true;
            }
            $this->db->update('sales', ['status' => 'partial', 'paid' => $paid], ['id' => $invoice_id]);
            return true;
        }
        return false;
    }

    public function addQuote($data = [], $items = [], $customer = [])
    {
        if (!empty($customer)) {
            if ($this->db->insert('customers', $customer)) {
                $customer_id = $this->db->insert_id();
            }
            $data['customer_id'] = $customer_id;
        }

        if ($this->db->insert('quotes', $data)) {
            $quote_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['quote_id'] = $quote_id;
                $this->db->insert('quote_items', $item);
            }

            return true;
        }

        return false;
    }

    public function addSale($data = [], $items = [], $customer = [])
    {
        if (!empty($customer)) {
            if ($this->db->insert('customers', $customer)) {
                $customer_id = $this->db->insert_id();
            }
            $data['customer_id'] = $customer_id;
        }

        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
            }

            if ($data['status'] == $this->lang->line('paid') || $data['status'] == 'paid') {
                $adata = [
                    'date'        => $data['date'],
                    'invoice_id'  => $sale_id,
                    'customer_id' => $data['customer_id'],
                    'amount'      => ($data['total'] + $data['shipping']),
                    'note'        => $this->lang->line('paid_nett'),
                    'user'        => $this->session->userdata('user_id')
                ];
                $this->db->insert('payment', $adata);
                $this->db->update('sales', ['paid' => ($data['total'] + $data['shipping'])], ['id' => $sale_id]);
            }

            return true;
        }

        return false;
    }

    public function addSalePayment($data, $sale)
    {
        if ($this->db->insert('payment', $data)) {
            $paid = $this->getPaidAmount($sale->id);

            if ($paid >= $sale->total) {
                $this->db->update('sales', ['status' => 'paid', 'paid' => $paid], ['id' => $sale->id]);
                return true;
            }
            $this->db->update('sales', ['status' => 'partial', 'paid' => $paid], ['id' => $sale->id]);
            return true;
        }
        return false;
    }

    public function deleteInvoice($id)
    {
        if ($this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sales', ['id' => $id])) {
            $this->db->delete('payment', ['invoice_id' => $id]);
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $payment = $this->getPaymentByID($id);
        $paid    = $this->getPaidAmount($payment->invoice_id);
        $paid    = $paid - $payment->amount;
        if ($this->db->delete('payment', ['id' => $id])) {
            $inv   = $this->getInvoiceByID($payment->invoice_id);
            $total = $inv->total + $inv->shipping;
            if ($paid >= $total) {
                $this->db->update('sales', ['status' => 'paid', 'paid' => $paid], ['id' => $payment->invoice_id]);
                return true;
            } elseif ($paid > 0) {
                $this->db->update('sales', ['status' => 'partial', 'paid' => $paid], ['id' => $payment->invoice_id]);
                return true;
            }
            $this->db->update('sales', ['status' => 'overdue', 'paid' => $paid], ['id' => $payment->invoice_id]);
            return true;
        }
        return false;
    }

    public function deleteQuote($id)
    {
        if ($this->db->delete('quote_items', ['quote_id' => $id]) && $this->db->delete('quotes', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllCompanies()
    {
        $q = $this->db->get('company');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCustomers()
    {
        $q = $this->db->get('customers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItems($sale_id)
    {
        $this->db->order_by('id');
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllQuoteItems($quote_id)
    {
        $this->db->order_by('id');
        $q = $this->db->get_where('quote_items', ['quote_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllQuotes()
    {
        $q = $this->db->get('quotes');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSales()
    {
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
   public function getLastSalesReferenceNo()
    {
        $this->db->select('reference_no');
        $this->db->order_by('id', 'DESC'); // Assuming 'id' is the primary key or an auto-increment field
        $this->db->limit(1);
        $q = $this->db->get('sales');
    
        if ($q->num_rows() > 0) {
            $row = $q->row(); // Fetch the single row
            $lastReferenceNo = $row->reference_no; // Get the last reference number
            $nextReferenceNo = $lastReferenceNo + 1; // Increment the last reference number by 1
            return $nextReferenceNo; // Return the next reference number
        }
    
        return false;
    }
    
     public function getAllSalesByDate()
    {
        
        $todayS = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+1 days', strtotime($todayS)));
        $newDueDate = date('Y-m-d', strtotime('+15 days', strtotime($dueDate))); // Calculate due date 2 days ahead
        
        $q = $this->db->where('due_date >=', $dueDate)
                      ->where('due_date <=', $newDueDate)
                    //   ->where('balon_status <=', $newDueDate)
                      ->order_by('due_date', 'ASC') 
                      ->get('sales');
        // $q = $this->db->get_where('sales', ['due_date' => $dueDate]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                
                
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('company', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyDetails()
    {
        $q = $this->db->get_where('company', ['id' => 1], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getConpmayByID($id)
    {
        $q = $this->db->get_where('company', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getDefaultNote($d = 'default_sale')
    {
        $q = $this->db->get_where('notes', [$d => 1]);
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

    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getmonthlySales()
    {
        $myQuery = "SELECT date_format( date, '%b' ) as month, SUM( total ) as sales FROM sales WHERE in_type = 'real' AND date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
        $q       = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getNotes($term, $limit = 5)
    {
        $this->db->like('description', $term, 'both');
        $this->db->limit($limit);
        $q = $this->db->get('notes');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payment', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaymentBySaleID($sale_id)
    {
        $this->db->order_by('id');
        $q = $this->db->get_where('payment', ['invoice_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', ['id' => $id], 1);
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

    public function getStripeSettings()
    {
        $q = $this->db->get('stripe');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function process_form()
    {
        $date                 = $this->sim->fld($this->input->post('date'));
        $due_date             = $this->input->post('due_date') ? $this->sim->fsd($this->input->post('due_date')) : null;
        $expiry_date          = $this->input->post('expiry_date') ? $this->sim->fsd($this->input->post('expiry_date')) : null;
        $reference_no         = $this->input->post('reference_no');
        $billing_company      = $this->input->post('billing_company');
        $company              = $this->getConpmayByID($billing_company);
        $billing_company_name = !empty($company->company) && $company->company != '-' ? $company->company : $company->name;
        $status               = $this->input->post('status');
        $shipping             = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
        $shipment             = $this->input->post('shipment') ;
        $order_discount_id    = $this->input->post('order_discount') ? $this->input->post('order_discount') : null;
        $order_tax_rate_id    = $this->input->post('order_tax') ? $this->input->post('order_tax') : null;
        $recurring            = $this->input->post('recurring');
        $customer_data        = [];
        $percentage           = '%';

        if ($this->input->post('customer') == 'new') {
            $customer_id   = 0;
            $customer_name = $this->input->post('company') ? $this->input->post('company') : $this->input->post('name');
            $customer_data = [
                'name'        => $this->input->post('name'),
                'email'       => $this->input->post('email'),
                'phone'       => $this->input->post('phone'),
                'company'     => $this->input->post('company'),
                'address'     => $this->input->post('address'),
                'city'        => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state'       => $this->input->post('state'),
                'country'     => $this->input->post('country')
            ];
        } else {
            $customer_id      = $this->input->post('customer');
            $customer_details = $this->sales_model->getCustomerByID($customer_id);
            $customer_name    = $customer_details->company ? $customer_details->company : $customer_details->name;
        }
        $note             = $this->input->post('note');
        $total            = 0;
        $product_discount = 0;
        $product_tax      = 0;

        $r = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
        for ($i = 0; $i < $r; $i++) {
            $item_name     = $_POST['product'][$i];
            $item_details  = $_POST['details'][$i];
            $item_price    = $_POST['price'][$i];
            $item_qty      = $_POST['quantity'][$i];
            $tax_method    = $_POST['tax_method'][$i] ?? 'exclusive';
            $item_discount = $_POST['discount'][$i]   ?? null;
            $item_tax_rate = $_POST['tax_rate'][$i]   ?? null;

            if (!empty($item_name) && !empty($item_price) && !empty($item_qty)) {
                $item_discount_amt = 0;
                $item_discount_val = $item_discount;
                if (!empty($item_discount)) {
                    $dpos = strpos($item_discount, $percentage);
                    if ($dpos !== false) {
                        $pds               = explode('%', $item_discount);
                        $item_discount_amt = $this->sim->formatDecimal(((($item_price) * (float) ($pds[0])) / 100), 4);
                    } else {
                        $item_discount_amt = $this->sim->formatDecimal(((float) $item_discount), 4);
                    }
                }
                $net_unit_price = $item_price - $item_discount_amt;
                $item_tax_amt   = 0;
                $item_tax_val   = 0;
                if (!empty($item_tax_rate) && $this->Settings->default_tax_rate && $tax_details = $this->sales_model->getTaxRateByID($item_tax_rate)) {
                    if ($tax_details->type == 1 && $tax_details->rate != 0) {
                        if ($tax_method == 'inclusive') {
                            $item_tax_amt = $this->sim->formatDecimal((($net_unit_price * $tax_details->rate) / (100 + $tax_details->rate)), 4);
                            $item_tax_val = $tax_details->rate . '%';
                            $net_unit_price -= $item_tax_amt;
                        } else {
                            $item_tax_amt = $this->sim->formatDecimal((($net_unit_price * $tax_details->rate) / 100), 4);
                            $item_tax_val = $tax_details->rate . '%';
                        }
                    } else {
                        $item_tax_amt = $this->sim->formatDecimal($tax_details->rate, 4);
                        $item_tax_val = $tax_details->rate;
                    }
                }

                $row_discount = $this->sim->formatDecimal(($item_discount_amt * $item_qty), 4);
                $row_tax      = $this->sim->formatDecimal(($item_tax_amt * $item_qty), 4);
                // $net_unit_price = ($tax_method == 'inclusive') ? $this->sim->formatDecimal(($item_price-$item_discount_amt-$item_tax_amt), 4) : $this->sim->formatDecimal(($item_price-$item_discount_amt), 4);
                $unit_price = $this->sim->formatDecimal(($net_unit_price + $item_tax_amt), 4);

                $product_discount += $row_discount;
                $product_tax      += $row_tax;
                $subtotal = $this->sim->formatDecimal(($item_qty * $unit_price), 4);

                $products[] = [
                    'product_name'    => $item_name,
                    'quantity'        => $item_qty,
                    'net_unit_price'  => $net_unit_price,
                    'unit_price'      => $unit_price,
                    'real_unit_price' => $item_price,
                    'subtotal'        => $subtotal,
                    'details'         => $item_details,
                    'tax_amt'         => $item_tax_amt,
                    'tax_rate_id'     => $item_tax_rate,
                    'tax'             => $item_tax_val,
                    'discount'        => $item_discount,
                    'discount_amt'    => $item_discount_amt,
                    'subtotal'        => $subtotal,
                    'tax_method'      => $tax_method,
                ];

                $total += $this->sim->formatDecimal(($subtotal), 4);
            }
        }

        if (empty($products)) {
            $this->form_validation->set_rules('product', lang('order_items'), 'required');
        }

        $order_discount = 0;
        if ($order_discount_id) {
            $opos = strpos($order_discount_id, $percentage);
            if ($opos !== false) {
                $ods            = explode('%', $order_discount_id);
                $order_discount = $this->sim->formatDecimal(((($total) * (float) ($ods[0])) / 100), 4);
            } else {
                $order_discount = $this->sim->formatDecimal($order_discount_id);
            }
        }

        $order_tax = 0;
        if ($order_tax_rate_id) {
            if ($order_tax_details = $this->sales_model->getTaxRateByID($order_tax_rate_id)) {
                if ($order_tax_details->type == 1 && $order_tax_details->rate != 0) {
                    $order_tax = $this->sim->formatDecimal(((($total - $order_discount) * $order_tax_details->rate) / 100), 4);
                } else {
                    $order_tax = $this->sim->formatDecimal($order_tax_details->rate, 4);
                }
            }
        }

        $total_discount = $this->sim->formatDecimal(($order_discount + $product_discount), 4);
        $total_tax      = $this->sim->formatDecimal(($product_tax + $order_tax), 4);
        $grand_total    = $this->sim->formatDecimal(($total + $order_tax + $this->sim->formatDecimal($shipping) - $order_discount), 4);

        $data = ['reference_no' => $reference_no,
            'company_id'        => $billing_company,
            'company_name'      => $billing_company_name,
            'date'              => $date,
            'due_date'          => $due_date,
            'recurring'         => $recurring,
            'expiry_date'       => $expiry_date,
            'user'              => $this->session->userdata('user_id'),
            'user_id'           => $this->session->userdata('user_id'),
            'customer_id'       => $customer_id,
            'customer_name'     => $customer_name,
            'product_discount'  => $product_discount,
            'order_discount_id' => $order_discount_id,
            'order_discount'    => $order_discount,
            'total_discount'    => $total_discount,
            'product_tax'       => $product_tax,
            'order_tax_id'      => $order_tax_rate_id,
            'order_tax'         => $order_tax,
            'total_tax'         => $total_tax,
            'total'             => $total,
            'grand_total'       => $grand_total,
            'status'            => $status,
            'shipping'          => $this->sim->formatDecimal($shipping),
            'note'              => $note,
            'shipment'          => $shipment,
        ];

        // $this->sim->print_arrays($data, $products, $customer_data);
        return ['data' => $data, 'products' => $products, 'customer_data' => $customer_data];
    }

    public function updatePayment($id, $data)
    {
        $payment = $this->getPaymentByID($id);
        $paid    = $this->getPaidAmount($payment->invoice_id);
        $paid    = $paid - $payment->amount + $data['amount'];
        if ($this->db->update('payment', $data, ['id' => $id])) {
            $inv   = $this->getInvoiceByID($payment->invoice_id);
            $total = $inv->total + $inv->shipping;
            if ($paid >= $total) {
                $this->db->update('sales', ['status' => 'paid', 'paid' => $paid], ['id' => $payment->invoice_id]);
                return true;
            }
            $this->db->update('sales', ['status' => 'partial', 'paid' => $paid], ['id' => $payment->invoice_id]);
            return true;
        }
        return false;
    }

    public function updateQuote($id, $data, $items = [], $customer = [])
    {
        if (!empty($customer)) {
            if ($this->db->insert('customers', $customer)) {
                $customer_id = $this->db->insert_id();
            }
            $data['customer_id'] = $customer_id;
        }

        if ($this->db->update('quotes', $data, ['id' => $id]) && $this->db->delete('quote_items', ['quote_id' => $id])) {
            foreach ($items as $item) {
                $item['quote_id'] = $id;
                $this->db->insert('quote_items', $item);
            }

            return true;
        }
        return false;
    }

    public function updateQuoteStatus($id)
    {
        if ($this->db->update('quotes', ['status' => 'sent'], ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateSale($id, $data, $items = [], $customer = [])
    {
        if (!empty($customer)) {
            if ($this->db->insert('customers', $customer)) {
                $customer_id = $this->db->insert_id();
            }
            $data['customer_id'] = $customer_id;
        }

        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id])) {
            foreach ($items as $item) {
                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
            }

            return true;
        }
        return false;
    }

    public function updateStatus($id, $status)
    {
        if ($this->db->update('sales', ['status' => $status], ['id' => $id])) {
            return true;
        }
        return false;
    }
    
    public function updateStatusBallon($id, $status)
    {
        if ($this->db->update('sales', ['ballon_status' => $status], ['id' => $id])) {
            return true;
        }
        return false;
    }
    
    
}
