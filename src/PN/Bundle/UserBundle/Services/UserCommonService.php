<?php

namespace PN\Bundle\UserBundle\Services;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use PN\Bundle\UserBundle\Entity\UserMobileVerify;
use PN\Bundle\UserBundle\Entity\User;
use PN\Utils\BulkSMS,
    PN\Utils\Number;

class UserCommonService {

    /**
     * @var TokenStorage
     */
    protected $container;

    /**
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get("doctrine")->getManager();
    }

    /**
     *
     * @param User $user
     * @param string(15) $newPhoneNumber low el user 3ayz ya3'er  el mobile number bat3o
     * @return boolean|string
     */
    public function sendSMSCode(User $user, $newPhoneNumber = null) {

//        $mobileCode = Number::generatePin(4);
        $mobileCode = 1234; //DEV-REMOVE
        if ($user->getUserMobileVerify() == null) {
            $userMobileVerify = new UserMobileVerify();
            $userMobileVerify->setUser($user);
            $userMobileVerify->setSmsNumber(1);
            $userMobileVerify->setMobileVerifyCode($mobileCode);
        } else {
            $userMobileVerify = $user->getUserMobileVerify();
            $sentTime = $userMobileVerify->getSentTime();
            $now = new \DateTime;
            $diff = $now->diff($sentTime);
            $diffInHour = ($diff->days * 24) + $diff->h;
            if ($diffInHour < 1 AND $userMobileVerify->getSmsNumber() >= 2) {
                return "Your SMS number exceeded, Please try again after one hour";
            }

            if ($diffInHour < 1) {
                $userMobileVerify->setSmsNumber($userMobileVerify->getSmsNumber() + 1);
            } else {
                $userMobileVerify->setMobileVerifyCode($mobileCode);
                $userMobileVerify->setSmsNumber(1);
            }
        }
        $phoneNumber = $user->getPhone();
        if ($newPhoneNumber != null) {
            $userMobileVerify->setNewPhoneNumber($newPhoneNumber);
            $phoneNumber = $userMobileVerify->getNewPhoneNumber();
        }
        $this->em->persist($userMobileVerify);
        $this->em->flush();

        $translator = $this->container->get("translator");
        $smsMessage = $translator->trans("Your verification code %code%", ["%code%" => $userMobileVerify->getMobileVerifyCode()]);
        BulkSMS::sendMessage($smsMessage, $phoneNumber);
        return TRUE;
    }

}
