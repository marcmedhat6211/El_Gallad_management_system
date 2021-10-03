<?php

namespace PN\Bundle\UserBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService {

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage    $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function getUser() {
        return $this->tokenStorage->getToken()->getUser();
    }

    public function getUserName() {
        $user = $this->getUser();
        if (method_exists($user, 'getFullName') == TRUE) {
            $userName = $user->getFullName();
        } else {
            $userName = $user->getUserName();
        }
        return $userName;
    }

}
