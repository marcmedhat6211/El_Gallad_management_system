<?php

namespace PN\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;

class Api {
    /*
     * @version      : 1
     * @author       : Merna Nagy <merna.nagy@perfectneeds.com>
     * Description   : Define the API response structure
     */

    protected static $translator;

    const HTTP_OK = 200;
    const HTTP_UNAUTHORIZED = 401;

    /**
     * ACCESS_DENIED
     */
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    /*
     * the request is missing a required parameter
     */
    const HTTP_UNPROCESSABLE_ENTITY = 422;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    private static function setTrans() {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        self::$translator = $kernel->getContainer()->get('translator');
    }

    /**
     *
     * @param type $status
     * @param type $code
     * @param type $errors
     * @param type $data
     * @param type $returnData
     * @return JsonResponse
     */
    public static function response($status, $code, $errors = [], $data = [], $returnData = null) {

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        if (count($errors) > 0) {
            self::setTrans();
            foreach ($errors as $erroKey => $error) {
                $errors[$erroKey] = self::$translator->trans($error);
            }
        }
        if ($returnData == "object") {
            return new JsonResponse(['code' => $code, 'errors' => $errors, 'data' => (object) $data], $status);
        }
        return new JsonResponse(['code' => $code, 'errors' => $errors, 'data' => $data], $status);
    }

}
