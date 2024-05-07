    <?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Customers extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->sim->logged_in()) {
            redirect('auth/login');
        }
        if ($this->sim->in_group('customer')) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('clients');
        }

        $this->load->library('form_validation');
        $this->load->model('customers_model');
    }

    public function add()
    {
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
         // $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'required|trim|valid_email|is_unique[customers.email]');
        // $this->form_validation->set_rules('company', $this->lang->line('company'), 'required');
        $this->form_validation->set_rules('address', $this->lang->line('address'), 'required');
        // $this->form_validation->set_rules('city', $this->lang->line('city'), 'required');
        // $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');

        if ($this->form_validation->run() == true) {
            $name    = strtolower($this->input->post('name'));
            $email   = $this->input->post('email');
            $company = $this->input->post('company');

            $data = ['name'   => $this->input->post('name'),
                'email'       => $this->input->post('email'),
                'company'     => $this->input->post('company'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'address'     => $this->input->post('address'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone')
            ];
        }

        if ($this->form_validation->run() == true && $this->customers_model->addCustomer($data)) {
            $this->session->set_flashdata('message', $this->lang->line('customer_added'));
            redirect('customers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['page_title'] = $this->lang->line('new_customer');
            $this->page_construct('customers/add', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->customers_model->getCustomerByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active                  = $this->input->post('status');
            list($username, $domain) = explode('@', $this->input->post('email'));
            $email                   = strtolower($this->input->post('email'));
            $password                = $this->input->post('password');
            $additional_data         = [
                'first_name'  => $this->input->post('first_name'),
                'last_name'   => $this->input->post('last_name'),
                'phone'       => $this->input->post('phone'),
                'customer_id' => $company->id,
                'company'     => $company->company,
            ];
            $group = ['4'];
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('customers');
        }
        $this->load->library('ion_auth');
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $group)) {
            $this->session->set_flashdata('message', $this->lang->line('user_added'));
            redirect('customers');
        } else {
            $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['company'] = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

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
            redirect('home');
        }

        if ($this->customers_model->deleteCustomer($id)) {
            $this->session->set_flashdata('message', $this->lang->line('customer_deleted'));
            redirect('customers');
        }
    }

    public function edit($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $customer = $this->customers_model->getCustomerByID($id);
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        // $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'required|trim|valid_email');
        // if ($customer->email != $this->input->post('email')) {
        //     $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[customers.email]');
        // }
        // $this->form_validation->set_rules('company', $this->lang->line('company'), 'required');
        $this->form_validation->set_rules('address', $this->lang->line('address'), 'required');
        // $this->form_validation->set_rules('city', $this->lang->line('city'), 'required');
        $this->form_validation->set_rules('phone', $this->lang->line('phone'), 'required|min_length[6]|max_length[16]');

        if ($this->form_validation->run() == true) {
            $data = ['name'   => $this->input->post('name'),
                'email'       => $this->input->post('email'),
                'company'     => $this->input->post('company'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'address'     => $this->input->post('address'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone')
            ];
        }

        if ($this->form_validation->run() == true && $this->customers_model->updateCustomer($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('customer_updated'));
            redirect('customers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['customer']   = $customer;
            $this->data['id']         = $id;
            $this->data['page_title'] = $this->lang->line('update_customer');
            $this->page_construct('customers/edit', $this->data);
        }
    }

    public function getdatatableajax()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select('id, name, company, phone, email, city, country')
        ->from('customers')

        ->add_column(
            'Actions',
            "<center><div class='btn-group'><a class=\"tip btn btn-primary btn-xs\" title='" . $this->lang->line('users') . "' href='" . site_url('customers/users/') . "?id=$1' data-toggle='ajax-modal'><i class=\"fa fa-users\"></i></a> <a class=\"tip btn btn-primary btn-xs\" title='" . $this->lang->line('add_user') . "' href='" . site_url('customers/add_user/') . "?id=$1' data-toggle='ajax-modal'><i class=\"fa fa-plus\"></i></a> <a class=\"tip btn btn-warning btn-xs\" title='" . $this->lang->line('edit_customer') . "' href='" . site_url('customers/edit/') . "?id=$1'><i class=\"fa fa-edit\"></i></a> <a class=\"tip btn btn-danger btn-xs\" title='" . $this->lang->line('delete_customer') . "' href='" . site_url('customers/delete/') . "?id=$1' onClick=\"return confirm('" . $this->lang->line('alert_x_customer') . "')\"><i class=\"fa fa-trash-o\"></i></a></div></center>",
            'id'
        )
        ->add_column(
            'login',
            "<center><div class='btn-group'><a class=\"tip btn btn-primary btn-xs\" target='_blank' title='" . $this->lang->line('login_as_customer') . "' href='" . site_url('clients/') . "?customer_id=$1'><i class=\"fa fa-sign-in\"></i></a></div></center>",
            'id'
        );

        echo $this->datatables->generate();
    }

    public function import()
    {
        if (!$this->sim->in_group('admin')) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('customers');
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', $this->lang->line('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
                redirect('home');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '200';
                $config['overwrite']     = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect('customers/import');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('uploads/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                // var_dump($arrResult); die();
                $keys = ['company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6'];

                $final = [];

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // var_dump($final); die();
                $rw = 2;
                foreach ($final as $csv_line) {
                    if (!empty($csv_line)) {
                        $data = $this->sim->escape_str($csv_line);
                        if ($customer = $this->customers_model->getCustomerByEmail($data['email'])) {
                            $this->customers_model->updateCustomer($customer->id, $data);
                        } else {
                            $this->customers_model->addCustomer($data);
                        }
                        $rw++;
                    }
                }
            }

            $this->session->set_flashdata('message', $this->lang->line('customers_added'));
            redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = ['name' => 'userfile',
                'id'                          => 'userfile',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('userfile')
            ];
            $this->data['page_title'] = $this->lang->line('import_customers');
            $this->page_construct('customers/upload_csv', $this->data);
        }
    }

    public function index()
    {
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = $this->lang->line('customers');
        $this->page_construct('customers/index', $this->data);
    }

    public function users($company_id = null)
    {
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['customer']   = $this->customers_model->getCustomerByID($company_id);
        $this->data['users']      = $this->customers_model->getCustomerUsers($company_id);
        $this->data['page_title'] = $this->lang->line('update_customer');
        $this->load->view($this->theme . 'customers/users', $this->data);
    }
}
