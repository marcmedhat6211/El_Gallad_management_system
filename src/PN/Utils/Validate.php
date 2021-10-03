<?php

namespace PN\Utils;

class Validate {
    /*
     * @version      : 1
     * @author       : Alex Seif <alex.seif@gmail.com>
     * Description   : function to test null for any type
     */

    public static function not_null($value, $length = null) {

        if ($value == '0') {
            return false;
        }
        if ($length != null and strlen($value) > $length) {
            return false;
        }
        if (is_array($value)) {
            if (sizeof($value) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if (($value != '') && (@strtolower($value) != 'null') && (@strlen(@trim($value)) > 0)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function date($date, $format = 'DD/MM/YYYY') {
        if ($format == 'YYYY-MM-DD') {
            list($year, $month, $day) = explode('-', $date);
        }
        if ($format == 'YYYY/MM/DD') {
            list($year, $month, $day) = explode('/', $date);
        }
        if ($format == 'YYYY.MM.DD') {
            list($year, $month, $day) = explode('.', $date);
        }

        if ($format == 'DD-MM-YYYY') {
            list($day, $month, $year) = explode('-', $date);
        }
        if ($format == 'DD/MM/YYYY') {
            list($day, $month, $year) = explode('/', $date);
        }
        if ($format == 'DD.MM.YYYY') {
            list($day, $month, $year) = explode('.', $date);
        }

        if ($format == 'MM-DD-YYYY') {
            list($month, $day, $year) = explode('-', $date);
        }
        if ($format == 'MM/DD/YYYY') {
            list($month, $day, $year) = explode('/', $date);
        }
        if ($format == 'MM.DD.YYYY') {
            list($month, $day, $year) = explode('.', $date);
        }

        if (is_numeric($year) && is_numeric($month) && is_numeric($day)) {
            return checkdate($month, $day, $year);
        }
        return false;
    }

    public static function email($email) {

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isPhoneNumber($number) {
        if (preg_match("/^[0-9\(\)\/\+ \-]+$/i", $number)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isJson($string) {
        if ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) {
            return true;
        } else {
            return false;
        }
    }

}
