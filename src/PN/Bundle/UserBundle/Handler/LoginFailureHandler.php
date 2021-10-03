<?php

namespace PN\Bundle\UserBundle\Handler;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class LoginFailureHandler implements EventSubscriberInterface {

    public static function getSubscribedEvents() {
        return ["kernel.exception" => ['catchException', 200]];
    }

    /**
     * @param GetResponseForExceptionEvent $evt
     */
    public function catchException(GetResponseForExceptionEvent $evt) {
        $request = $evt->getRequest()->getRequestFormat();
        $url=$evt->getRequest()->getRequestUri();

        if ($request != 'json' ) {
            return;
        }elseif( strpos($url,"/login") === FALSE)
        {
            return;

        }
        $username = $evt->getRequest()->get('_username');
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $user = $kernel->getContainer()->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        if ($user != NULL AND ! $user->isEnabled()) {
            $returnArray = array("error" => 1, 'message' => 'Your account is not active');
        } else {
            $returnArray = array("error" => 1, 'message' => 'invalid username or password');
        }
        $response = new Response(json_encode($returnArray));
        $evt->stopPropagation();  // stop the other listener to redirect to login
        $evt->setResponse($response);
    }

}
