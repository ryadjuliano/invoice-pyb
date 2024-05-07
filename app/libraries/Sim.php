<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
 *  ==============================================================================
 *  Author  : Mian Saleem
 *  Email   : saleem@tecdiary.com
 *  For     : Simple Invoice Manager
 *  Web     : http://tecdiary.com
 *  ==============================================================================
 */

class Sim
{
    public function __construct()
    {
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function clear_tags($str)
    {
        return htmlentities(
            strip_tags(
                $str,
                '<span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><big><pre><code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>'
            ),
            ENT_QUOTES | ENT_XHTML | ENT_HTML5,
            'UTF-8'
        );
    }

    public function decode_html($str)
    {
        return html_entity_decode($str, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8');
    }

    public function escape_str($q)
    {
        if (is_array($q)) {
            foreach ($q as $k => $v) {
                $q[$k] = $this->escape_str($v);
            }
        } elseif (is_string($q)) {
            $q = $this->db->escape_str($q);
        }
        return $q;
    }

    public function fld($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time     = $date[1];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2) . ' ' . $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2) . ' ' . $time;
            } else {
                $date = $inv_date;
            }
            return $date;
        }
        return '0000-00-00 00:00:00';
    }

    public function formatDecimal($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        return number_format($number, $decimals, '.', '');
    }

    public function formatMoney($number, $currency = '')
    {
        $decimal = $this->Settings->decimals;
        $ts      = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds      = $this->Settings->decimals_sep;
        return $currency . number_format($number, $decimal, $ds, $ts);
    }

    public function formatNumber($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function fsd($inv_date)
    {
        if ($inv_date) {
            $jsd = $this->dateFormats['js_sdate'];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            }
            return $date;
        }
        return '0000-00-00';
    }

    public function generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P')
    {
        $this->load->helper('file');
        $this->load->library('tec_dompdf');
        return $this->tec_dompdf->generate($content, $name, $output_type, $footer, $margin_bottom, $header, $margin_top, $orientation);
    }

    public function get_image($path)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    public function hrld($ldate)
    {
        if ($ldate) {
            return date($this->dateFormats['php_ldate'], strtotime($ldate));
        }
        return '0000-00-00 00:00:00';
    }

    public function hrsd($sdate)
    {
        if ($sdate) {
            return date($this->dateFormats['php_sdate'], strtotime($sdate));
        }
        return '0000-00-00';
    }

    public function in_group($check_group, $id = false)
    {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $group = $this->site->getUserGroup($id);
        if ($group && $group->name === $check_group) {
            return true;
        }
        return false;
    }

    public function logged_in()
    {
        return (bool) $this->session->userdata('identity');
    }

    public function print_arrays()
    {
        $args = func_get_args();
        echo '<pre>';
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo '</pre>';
        die();
    }

    public function roundMoney($num, $nearest = 0.05)
    {
        return round($num * (1 / $nearest)) * $nearest;
    }

    public function send_email($to, $subject, $message, $from = null, $from_name = null, $attachment = null, $cc = null, $bcc = null)
    {
        list($user, $domain) = explode('@', $to);
        if ($domain != 'tecdiary.com') {
            $result = false;
            $this->load->library('tec_mail');
            try {
                $result = $this->tec_mail->send_mail($to, $subject, $message, $from, $from_name, $attachment, $cc, $bcc);
            } catch (\Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                // throw new \Exception($e->getMessage());
            }
            return $result;
        }
    }

    public function send_json($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
        exit;
    }

    public function unset_data($ud)
    {
        if ($this->session->userdata($ud)) {
            $this->session->unset_userdata($ud);
            return true;
        }
        return false;
    }

    public function unzip($source, $destination = './')
    {
        // @chmod($destination, 0777);
        $zip = new ZipArchive();
        if ($zip->open(str_replace('//', '/', $source)) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
        // @chmod($destination,0755);

        return true;
    }

    public function view_rights($user_id)
    {
        if (!$this->Admin) {
            if ($user_id != $this->session->userdata('user_id')) {
                $this->session->set_flashdata('warning', lang('access_denied'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
        return true;
    }

    public function zip($source = null, $destination = './', $output_name = 'sma', $limit = 5000)
    {
        if (!$destination || trim($destination) == '') {
            $destination = './';
        }

        $this->_rglobRead($source, $input);
        $maxinput  = count($input);
        $splitinto = (($maxinput / $limit) > round($maxinput / $limit, 0)) ? round($maxinput / $limit, 0) + 1 : round($maxinput / $limit, 0);

        for ($i = 0; $i < $splitinto; $i++) {
            $this->_zip(array_slice($input, ($i * $limit), $limit, true), $i, $destination, $output_name);
        }

        unset($input);
    }

    private function _rglobRead($source, &$array = [])
    {
        if (!$source || trim($source) == '') {
            $source = '.';
        }
        foreach ((array) glob($source . '/*/') as $key => $value) {
            $this->_rglobRead(str_replace('//', '/', $value), $array);
        }
        $hidden_files = glob($source . '.*') and $htaccess = preg_grep('/\.htaccess$/', $hidden_files);
        $files        = array_merge(glob($source . '*.*'), $htaccess);
        foreach ($files as $key => $value) {
            $array[] = str_replace('//', '/', $value);
        }
    }

    private function _zip($array, $part, $destination, $output_name = 'sma')
    {
        $zip = new ZipArchive();
        @mkdir($destination, 0777, true);

        if ($zip->open(str_replace('//', '/', "{$destination}/{$output_name}" . ($part ? '_p' . $part : '') . '.zip'), ZipArchive::CREATE)) {
            foreach ((array) $array as $key => $value) {
                $zip->addFile($value, str_replace(['../', './'], null, $value));
            }
            $zip->close();
        }
    }
}
