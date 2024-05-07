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
| MODULE:           Customers
| -----------------------------------------------------
| This is customers module's model file.
| -----------------------------------------------------
*/

class Customers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addCustomer($data = [])
    {
        if ($this->db->insert('customers', $data)) {
            $customer_id = $this->db->insert_id();
            if ($this->Settings->customer_user) {
                list($username, $domain)      = explode('@', $data['email']);
                list($first_name, $last_name) = explode(' ', $data['name'], 2);
                $email                        = strtolower($data['email']);
                $this->load->helper('string');
                $password        = random_string('alnum', 8);
                $additional_data = [
                    'first_name'  => $first_name,
                    'last_name'   => $last_name,
                    'phone'       => $data['phone'],
                    'customer_id' => $customer_id,
                    'company'     => $data['company'],
                ];
                $group = ['4'];
                $this->load->library('ion_auth');
                $this->ion_auth->register($username, $password, $email, $additional_data, $group);
            }
            return true;
        }
        return false;
    }

    public function customers_count()
    {
        return $this->db->count_all('customers');
    }

    public function deleteCustomer($id)
    {
        if ($this->db->delete('customers', ['id' => $id])) {
            $this->db->delete('users', ['customer_id' => $id]);
            return true;
        }
        return false;
    }

    public function fetch_customers($limit, $start)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('customers');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
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

    public function getCustomerByEmail($email)
    {
        $q = $this->db->get_where('customers', ['id' => $email], 1);
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

    public function getCustomerUsers($customer_id)
    {
        $q = $this->db->get_where('users', ['customer_id' => $customer_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateCustomer($id, $data = [])
    {
        if ($this->db->update('customers', $data, ['id' => $id])) {
            $company_name = (!empty($data['company']) && $data['company'] != '-') ? $data['company'] : $data['name'];
            $this->db->update('sales', ['customer_name' => $company_name], ['customer_id' => $id]);
            $this->db->update('quotes', ['customer_name' => $company_name], ['customer_id' => $id]);
            return true;
        }
        return false;
    }
}
