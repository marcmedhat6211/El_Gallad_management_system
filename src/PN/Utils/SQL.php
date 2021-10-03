<?php

namespace PN\Utils;

use PN\Utils\Validate;

/**
 * Filename      : general
 * Requires      : PHP 5.0+
 * @version      : 3
 * @author       : Peter Samy <peter.samy@gmail.com>
 * Date released : 1/11/2012
 * @license      : license
 * @package      : general
 * Purpose       :
 * Description   : -
 *
 */
class SQL {

    var $txt;
    var $params = array();
    var $idx = 0;

    public static function inCreateDirect($prmtrValuesArr, $columnName, $not = FALSE, $inT = ' IN ') {
        $temp = '';
        $realElementExist = FALSE;
        $in = FALSE;
        $return = '';

        while (count($prmtrValuesArr) > 0) {
            $value = @array_pop($prmtrValuesArr);
            if (Validate::not_null($value)) {
                $in = $in ? ' , ' : $columnName . $inT . ' ( ';
                $sqlCondition = $in . (int) $value;
                $temp .= $sqlCondition;
                $realElementExist = TRUE;
            }
        }

        $return .= $temp . ') ';
        return ( $realElementExist ) ? $return : '';
    }

    public static function inCreate($prmtrValuesArr, $columnName, &$where, $condition = ' AND ', $inT = ' IN ') {
        $temp = '';
        $realElementExist = FALSE;
        $in = FALSE;

        while (count($prmtrValuesArr) > 0) {
            $value = @array_pop($prmtrValuesArr);
            if (Validate::not_null($value)) {
                $in = $in ? ' , ' : $columnName . $inT . ' ( ';
                $sqlCondition = $in . (int) $value;
                $temp .= $sqlCondition;
                $realElementExist = TRUE;
            }
        }
        if ($realElementExist) {
            $where = ($where) ? $condition : ' WHERE ';
            return $where . $temp . ' ) ';
        } else {
            return '';
        }
    }

//SEARCH SENTENCE CLAUSE GENERATOR
    public static function searchSCG($searchString, $field, $andor) {
        $searchArr = explode(",", $searchString);
        $LIKE = FALSE;
        $temp = '(';
        foreach ($searchArr as $searchSentence) {
            if (self::validateSS($searchSentence)) {
                if($LIKE){
                    $sqlCondition = ' OR '.$field . ' LIKE "%' . trim($searchSentence) . '%"';
                }else{
                    $sqlCondition = $field . ' LIKE "%' . trim($searchSentence) . '%"';
                    $LIKE=TRUE;
                }



                $temp .= $sqlCondition;
            }
        }

        return $andor . $temp . ")";
    }

//VALIDATE SEARCH SENTENCE
    public static function validateSS($searchSentence) {
        if (!Validate::not_null($searchSentence) || count(array($searchSentence)) < 1)
            return FALSE;
        return self::setRegEXP(trim($searchSentence));
    }

    public static function setRegEXP($value) {
        return ($value);
    }
    public static function strToDateCreate($str, $format) {
        return "STR_TO_DATE('" . $str . "','" . $format . "')";
    }
}
