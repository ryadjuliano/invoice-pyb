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
| WEBSITE:          http://tecdiary.net
| -----------------------------------------------------
|
| MODULE:           Settings
| -----------------------------------------------------
| This is products module model file.
| -----------------------------------------------------
*/

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addCompany($data = [])
    {
        if ($this->db->insert('company', $data)) {
            return true;
        }
        return false;
    }

    public function addNote($data)
    {
        if ($this->db->insert('notes', $data)) {
            return true;
        }
        return false;
    }

    public function addTaxRate($data)
    {
        if ($this->db->insert('tax_rates', $data)) {
            return true;
        }
        return false;
    }

    public function deleteCompany($id)
    {
        if ($this->db->delete('company', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteNote($id)
    {
        if ($this->db->delete('notes', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteTaxRate($id)
    {
        if ($this->db->delete('tax_rates', ['id' => $id])) {
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

    public function getAllNotes()
    {
        $q = $this->db->get('notes');
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

    public function getCustomerByID($id)
    {
        $q = $this->db->get_where('customers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormats()
    {
        $q = $this->db->get('date_format');
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

    public function getSettings()
    {
        $q = $this->db->get('settings');
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

    public function getUserByFirstName($fn)
    {
        $q = $this->db->get_where('users', ['first_name' => $fn], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateCompany($id, $data = [])
    {
        if ($this->db->update('company', $data, ['id' => $id])) {
            $company_name = (!empty($data['company']) && $data['company'] != '-') ? $data['company'] : $data['name'];
            $this->db->update('sales', ['company_name' => $company_name], ['company_id' => $id]);
            $this->db->update('quotes', ['company_name' => $company_name], ['company_id' => $id]);
            return true;
        }
        return false;
    }

    public function updateInvoiceLogo($photo)
    {
        $logo = ['invoice_logo' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateLogo($photo)
    {
        $logo = ['logo' => $photo];
        if ($this->db->update('settings', $logo)) {
            return true;
        }
        return false;
    }

    public function updateNote($id, $data)
    {
        if ($this->db->update('notes', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updatePaypal($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('paypal', $data)) {
            return true;
        }
        return false;
    }

    public function updateQuotesUser()
    {
        $quotes = $this->getAllQuotes();
        foreach ($quotes as $quote) {
            if ($user = $this->getUserByFirstName($quote->user)) {
                $this->db->update('quotes', ['user' => $user->id], ['id' => $quote->id]);
            }
        }
        return true;
    }

    public function updateSalesUser()
    {
        $sales = $this->getAllSales();
        foreach ($sales as $sale) {
            if ($user = $this->getUserByFirstName($sale->user)) {
                $this->db->update('sales', ['user' => $user->id], ['id' => $sale->id]);
            }
        }
        return true;
    }

    public function updateSetting($data)
    {
        $this->db->where('setting_id', '1');
        if ($this->db->update('settings', $data)) {
            return true;
        }
        return false;
    }

    public function updateSkrill($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('skrill', $data)) {
            return true;
        }
        return false;
    }

    public function updateStripe($data)
    {
        $this->db->where('id', '1');
        if ($this->db->update('stripe', $data)) {
            return true;
        }
        return false;
    }

    public function updateTaxRate($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('tax_rates', $data)) {
            return true;
        }
        return false;
    }
}
