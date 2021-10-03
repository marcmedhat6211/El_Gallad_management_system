<?php

namespace PN\Utils;

/**
 * Send sms using voodoo.com
 */
class BulkSMS {

    private static $username = '';
    private static $password = '';
    private static $origin = 'Deepo';
    private static $url = 'http://www.voodoosms.com/vapi/server/sendSMS';

    /*
     * Your phone number, including country code, i.e. +44123123123 in this case:
     */

    public static function sendMessage($message, $mobileNumber) {
//        dump($message . "-" . $mobileNumber);
//        $mail = array(
//            'subject' => 'SMS Verfiy code',
//            'from' => array('no-reply@alex-app.com' => 'Alex'),
//            'to' => array($mobileNumber),
//            'body' => $message
//        );
//        Mailer::sendEmail($mail);
//        return;

        $params = [
            'dest' => $mobileNumber,
            'orig' => self::$origin,
            'msg' => $message,
            'uid' => self::$username,
            'pass' => self::$password,
            'format' => 'json',
            'cc' => 44,
        ];

        $url = self::$url . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseJson = json_decode($response);
        if ($responseJson->result != 200) {
            $mail = array(
                'subject' => 'Voodo SMS Error',
                'from' => array('no-reply@alex-app.com' => 'Alex'),
                'to' => 'peter.nassef@gmail.com',
                'body' => "<h2>Error</h2>"
                . "<br />"
                . '<b>Responce:</b> "' . $response . '"'
                . "<br />"
                . '<b>URL</b> "' . $url . '"'
            );
            Mailer::sendEmail($mail);
        }
        return $response;
    }

}

?>
