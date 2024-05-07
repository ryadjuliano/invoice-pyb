<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
| -----------------------------------------------------
| PRODUCT NAME:     STOCK MANAGER ADVANCE
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
| MODULE:           Products
| -----------------------------------------------------
| This is products module's model file.
| -----------------------------------------------------
*/

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_products($data = [])
    {
        if ($this->db->insert_batch('products', $data)) {
            return true;
        }
        return false;
    }

    public function addProduct($data = [])
    {
        if ($this->db->insert('products', $data)) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        if ($this->db->delete('products', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function fetch_products($limit, $start)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('products');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
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
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
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

    public function getProductNames($term, $limit = 5)
    {
        $this->db->like('name', $term, 'both')->or_like('details', $term, 'both');
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function products_count()
    {
        return $this->db->count_all('products');
    }

    public function updateProduct($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('products', $data)) {
            return true;
        }
        return false;
    }
}
