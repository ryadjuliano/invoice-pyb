<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar extends MY_Controller
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
        $this->load->model('calendar_model');
    }

    public function add_event()
    {
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->sim->fld($this->input->post('start')),
                'end'         => $this->input->post('end') ? $this->sim->fld($this->input->post('end')) : null,
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'user_id'     => $this->session->userdata('user_id')
            ];

            if ($this->calendar_model->addEvent($data)) {
                $res = ['error' => 0, 'msg' => lang('event_added')];
                $this->sim->send_json($res);
            } else {
                $res = ['error' => 1, 'msg' => lang('action_failed')];
                $this->sim->send_json($res);
            }
        }
    }

    public function delete_event($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->is_ajax_request()) {
            if ($event = $this->calendar_model->getEventByID($id)) {
                if (!$this->sim->in_group('admin') && $event->user_id != $this->session->userdata('user_id')) {
                    $res = ['error' => 1, 'msg' => lang('access_denied')];
                    $this->sim->send_json($res);
                }
                $this->db->delete('calendar', ['id' => $id]);
                $res = ['error' => 0, 'msg' => lang('event_deleted')];
                $this->sim->send_json($res);
            }
        }
    }

    public function get_cal_lang()
    {
        return 'en';
    }

    public function get_day_event($date)
    {
        $this->data['events'] = $this->calendar_model->getDayEvents($date);
        $this->data['date']   = $date;
        echo $this->load->view($this->theme . 'events', $this->data, true);
    }

    public function get_events()
    {
        $cal_lang = $this->get_cal_lang();
        $this->load->library('fc', ['lang' => $cal_lang]);

        if (!isset($_GET['start']) || !isset($_GET['end'])) {
            die('Please provide a date range.');
        }

        if ($cal_lang == 'ar') {
            $start = $this->fc->convert2($this->input->get('start', true));
            $end   = $this->fc->convert2($this->input->get('end', true));
        } else {
            $start = $this->input->get('start', true);
            $end   = $this->input->get('end', true);
        }

        $input_arrays  = $this->calendar_model->getEvents($start, $end);
        $start         = $this->fc->parseDateTime($start);
        $end           = $this->fc->parseDateTime($end);
        $output_arrays = [];
        foreach ($input_arrays as $array) {
            $this->fc->load_event($array);
            if ($this->fc->isWithinDayRange($start, $end)) {
                $output_arrays[] = $this->fc->toArray();
            }
        }

        $this->sim->send_json($output_arrays);
    }

    public function index()
    {
        $this->data['cal_lang']   = $this->get_cal_lang();
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('calendar')]];
        $meta                     = ['page_title' => lang('calendar'), 'bc' => $bc];
        $this->data['page_title'] = lang('calendar');
        $this->page_construct('calendar', $this->data, $meta);
    }

    public function update_event()
    {
        $this->form_validation->set_rules('title', lang('title'), 'trim|required');
        $this->form_validation->set_rules('start', lang('start'), 'required');

        if ($this->form_validation->run() == true) {
            $id = $this->input->post('id');
            if ($event = $this->calendar_model->getEventByID($id)) {
                if (!$this->sim->in_group('admin') && $event->user_id != $this->session->userdata('user_id')) {
                    $res = ['error' => 1, 'msg' => lang('access_denied')];
                    $this->sim->send_json($res);
                }
            }
            $data = [
                'title'       => $this->input->post('title'),
                'start'       => $this->sim->fld($this->input->post('start')),
                'end'         => $this->input->post('end') ? $this->sim->fld($this->input->post('end')) : null,
                'description' => $this->input->post('description'),
                'color'       => $this->input->post('color') ? $this->input->post('color') : '#000000',
                'user_id'     => $this->session->userdata('user_id')
            ];

            if ($this->calendar_model->updateEvent($id, $data)) {
                $res = ['error' => 0, 'msg' => lang('event_updated')];
                $this->sim->send_json($res);
            } else {
                $res = ['error' => 1, 'msg' => lang('action_failed')];
                $this->sim->send_json($res);
            }
        }
    }
}
