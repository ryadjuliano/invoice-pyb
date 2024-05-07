<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use NumberToWords\NumberToWords;
use NumberToWords\Exception\NumberToWordsException;

class Tec_n2w
{
    private $ct;

    private $n2w;

    private $nt;

    public function __construct($config)
    {
        $this->n2w = new NumberToWords();
        $lang      = $this->getLanguageCode($config['lang']);
        $this->nt  = $this->n2w->getNumberTransformer($lang);
        $this->ct  = $this->n2w->getCurrencyTransformer($lang);
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function c2w($amt = false)
    {
        if (!$this->Settings->display_words) {
            return '';
        }
        try {
            $result = $amt ? ucfirst($this->ct->toWords(($amt * 100), $this->Settings->currency_prefix)) : '';
        } catch (NumberToWordsException $e) {
            $result = '';
        } catch (\Exception $e) {
            $result = '';
        }
        return $result;
    }

    public function getLanguageCode($lang)
    {
        switch ($lang) {
            case 'arabic':
                $code = 'ar';
                break;
            case 'french':
                $code = 'fr';
                break;
            case 'german':
                $code = 'de';
                break;
            case 'italian':
                $code = 'it';
                break;
            case 'portuguese-brazilian':
                $code = 'pt_BR';
                break;
            case 'spanish':
                $code = 'es';
                break;
            case 'thai':
                $code = 'th';
                break;
            case 'turkish':
                $code = 'tr';
                break;
            case 'vietnamese':
                $code = 'vi';
                break;
            default:
                $code = 'en';
                break;
        }
        return $code;
    }

    public function n2w($amt = false)
    {
        try {
            $result = $amt ? ucfirst($this->nt->toWords(($amt * 100), $this->Settings->currency_prefix)) : '';
        } catch (NumberToWordsException $e) {
            $result = '';
        } catch (\Exception $e) {
            $result = '';
        }
        return $result;
    }

    public function setCT($lang)
    {
        $this->nt = $this->n2w->getNumberTransformer($lang);
    }

    public function setNT($lang)
    {
        $this->nt = $this->n2w->getNumberTransformer($lang);
    }
}
