<?php

namespace PN\Utils;

Use Symfony\Component\HttpFoundation\Session\Session;

class Number {
    /*
     * @version      : 1
     * @author       : Peter Soliman <peter.samy@gmail.com>
     * Description   : function to Display Money Formate 
     */

    public static function money($number, $currency = 'USD', $dec_point = '.', $thousands_sep = '') {
        if ($currency)
            return number_format($number, 2, $dec_point, $thousands_sep) . $currency;
        return number_format($number, 2, $dec_point, $thousands_sep);
    }

    /*
     * @version      : 1
     * @author       : Peter Soliman <peter.samy@gmail.com>
     * Description   : function to Display Orders, Bookings, ... Formate 
     */

    public static function code($number, $prefix = "#", $padLengt = 12, $padString = '0') {
        return $prefix . str_pad($number, $padLengt, $padString, STR_PAD_LEFT);
    }

    /*
     * @version      : 1
     * @author       : Peter Nassef <peter.nassef@gmail.com>
     * Description   : function to add currecy symbol(session) with romat 
     */

    public static function currencyWithFormat($price, $currency = NULL) {
        if ($currency == NULL) {
            $session = new Session();
            $currency = $session->get('currency');
        }
        $symbol = $currency->getSymbol();
        return number_format((float) $price, 2) . ' ' . $symbol;
    }

    public static function generatePin($lenght = 5) {
        $pin = substr(str_shuffle(str_repeat("0123456789", $lenght)), 0, $lenght);
        if (substr($pin, 0, 1) == '0') {
            $pin = ltrim($pin, '0');
            $pin = '2' . $pin;
        }
        return $pin;
    }

}
