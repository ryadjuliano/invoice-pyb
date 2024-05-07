<?php

(defined('BASEPATH')) or exit('No direct script access allowed');

class MY_Lang extends CI_Lang
{
    public function __construct()
    {
        parent::__construct();
    }

    public function line($line, $params = null)
    {
        $return = parent::line($line);
        if ($return === false) {
            return str_replace('_', ' ', $line);
        }
        if (!is_null($params)) {
            $return = $this->_ni_line($return, $params);
        }
        return $return;
    }

    private function _ni_line($str, $params)
    {
        $return = $str;
        $params = is_array($params) ? $params : [$params];
        $search = [];
        $cnt    = 1;
        foreach ($params as $param) {
            $search[$cnt] = "/\\${$cnt}/";
            $cnt++;
        }
        unset($search[0]);
        $return = preg_replace($search, $params, $return);
        return $return;
    }
}
