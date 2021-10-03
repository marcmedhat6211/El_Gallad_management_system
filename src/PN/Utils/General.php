<?php

namespace PN\Utils;

class General {
    /*
     * @version      : 1
     * @author       : Peter Soliman <peter.samy@gmail.com>
     * Description   : function to Convert Array of Objects to One dimentional array
     */

    public static function objectsToArr($objectsARR, $varName) {
        $arrayVarValues = array();
        foreach ($objectsARR as $object) {
            array_push($arrayVarValues, $object->$varName);
        }
        return $arrayVarValues;
    }

    /*
     * @version      : 1
     * @author       : Peter Soliman <peter.samy@gmail.com>
     * Description   : checks if a given date is included in an interval
     */

    public static function dateInBetween($date, $startDate, $endDate) {
        if ((strtotime($date) - strtotime($startDate)) >= 0 && (strtotime($endDate) - strtotime($date)) >= 0)
            return TRUE;
        else
            return FALSE;
    }

    /*
     * @version      : 1
     * @author       : Peter Soliman <peter.samy@gmail.com>
     * Description   : checks if a given date is included in an interval
     */

    public static function slug($string) {
        return self::toAscii(trim($string));
    }

    /**
     *
     * @param string $str
     * @param array $replace
     * @param string $delimiter
     * @return string
     */
    public static function toAscii($str, $replace = array(), $delimiter = '-') {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    /*
     * @version      : 1
     * @author       : Peter Boshra <peteracmilan@gmail.com>
     * Description   : checks if a given date is included in an interval
     * attributes    : DateTime  objects
     */

    public static function dateDiffrence($startDate, $endDate) {
        return $endDate - $startDate;
    }

    /*
     * @version      : 1
     * @author       : Peter Boshra <peteracmilan@gmail.com>
     * Description   :return string like that peter-boshra
     * attributes    :string
     */

    public static function seoUrl($string) {
        return Slug::sanitize($string);
    }

    /*
     * @version      : 1
     * @author       : Bishoy Yahya <bishocis@gmail.com>
     * Description   :return months witn 2 langauages
     * attributes    :string  
     */

    public static function yearMonths() {
        $months = array(
            1 => array("Ar" => "يناير", "En" => "January"),
            2 => array("Ar" => "فبراير", "En" => "February"),
            3 => array("Ar" => "مارس", "En" => "March"),
            4 => array("Ar" => "ابريل", "En" => "April"),
            5 => array("Ar" => "مايو", "En" => "May"),
            6 => array("Ar" => "يونيو", "En" => "June"),
            7 => array("Ar" => "يوليو", "En" => "July"),
            8 => array("Ar" => "اغسطس", "En" => "August"),
            9 => array("Ar" => "سبتمبر", "En" => "September"),
            10 => array("Ar" => "اكتوبر", "En" => "October"),
            11 => array("Ar" => "نوفمبر", "En" => "November"),
            12 => array("Ar" => "ديسيمبر", "En" => "December"),
        );
        return $months;
    }

    /*
     * @version      : 1
     * @author       : Peter Nassef <peter.nassef@gmail.com>
     * Description   :return translated date
     * attributes    :string
     */

    public static function transDateToArabic($date) {

        $transFrom = array(
            "January",
            "Jan",
            "February",
            "Feb",
            "March",
            "Mar",
            "April",
            "Apr",
            "May",
            "June",
            "Jun",
            "July",
            "Jul",
            "August",
            "Aug",
            "September",
            "Sept",
            "October",
            "Oct",
            "November",
            "Nov",
            "December",
            "Dec",
            "Sunday",
            "Saturday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "AM",
            "PM",
            "am",
            "pm",
        );
        $transTo = array(
            "يناير",
            "يناير",
            "فبراير",
            "فبراير",
            "مارس",
            "مارس",
            "ابريل",
            "ابريل",
            "مايو",
            "يونيو",
            "يونيو",
            "يوليو",
            "يوليو",
            "اغسطس",
            "اغسطس",
            "سبتمبر",
            "سبتمبر",
            "اكتوبر",
            "اكتوبر",
            "نوفمبر",
            "نوفمبر",
            "ديسيمبر",
            "ديسيمبر",
            "السبت",
            "الأحد",
            "الأثنين",
            "الثلاثاء",
            "الأربعاء",
            "الخميس",
            "الجمعة",
            "ص",
            "م",
            "ص",
            "م",
        );
        return str_replace($transFrom, $transTo, $date);
    }

    public function rawText($str, $length = null) {
        $str = strip_tags($str);
        if ($length != null AND strlen($str) > $length) {
            $str = htmlspecialchars_decode(substr($str, 0, strpos(wordwrap($str, $length), "\n"))) . '...';
        }
        return $str;
    }

    public static function generateRandString() {
        return md5(uniqid(rand(), true));
    }

    public static function fileSizeConvert($bytes) {

        $result = "";

        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    public static function fromCamelCaseToUnderscore($input) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

}
