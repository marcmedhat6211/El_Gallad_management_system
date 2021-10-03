<?php

namespace PN\Utils;

class SearchText
{

    public static function makeSearchableKeywords(array $keywordsArray)
    {
        if (count($keywordsArray) == 0) {
            return null;
        }
        $keywordsArray = array_filter($keywordsArray, 'strlen');

        foreach ($keywordsArray as $key => $value) {
            $keywordsArray[$key] = self::normalizeKeyword($value);
        }
        $cleanWordsWithoutDuplication = self::removeDuplicateWords($keywordsArray);
        $keywords = implode(' ', $cleanWordsWithoutDuplication);
        $keywords = self::removeExtraSpaces($keywords);

        return $keywords;
    }

    private static function removeExtraSpaces($keywords)
    {
        return str_replace("  ", " ", $keywords);
    }

    private static function removeDuplicateWords(array $strs)
    {
        $string = implode(" ", $strs);
        $strings = explode(' ', $string);
        $strings = array_unique(array_filter($strings, 'strlen'));

        //        $uniqe = [];
        //        foreach ($strings as $value) {
        //            if (strpos($string, $value) !== false) {
        //                $uniqe[] = $value;
        //                $string = str_replace($value, "", $string);
        //            }
        //        }
        return $strings;
    }

    public static function normalizeKeyword($keyword)
    {
        $normalized = self::normalizeArabic($keyword);
        $normalized = self::normalizeEnglish($normalized);
        $normalized = self::removeSpecialCharacters($normalized);

        return $normalized;
    }

    private static function removeSpecialCharacters($keyword)
    {
        $keyword = str_replace("-", " ", $keyword);
        $specialChars = array(
            "(",
            "&",
            "'",
            "\"",
            "/",
            "\\",
            "%",
            "*",
            "#",
            ")",
        );

        return str_replace($specialChars, "", $keyword);
    }

    private static function normalizeArabic($keyword)
    {
        $patterns = array("/إ|أ|آ/", "/ة/", "/َ|ً|ُ|ِ|ٍ|ٌ|ّ/");
        $replacements = array("ا", "ه", "");
        $keyword = preg_replace($patterns, $replacements, $keyword);

        return self::normalizeArabicNumbers($keyword);
    }

    private static function normalizeArabicNumbers($keyword)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(9, 0);
        $convertedPersianNums = str_replace($persian, $num, $keyword);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }

    private static function normalizeEnglish($keyword)
    {
        return strtolower($keyword);
    }

}

?>