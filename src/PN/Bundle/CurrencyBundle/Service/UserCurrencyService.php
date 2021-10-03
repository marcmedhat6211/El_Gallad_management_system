<?php

namespace PN\Bundle\CurrencyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class UserCurrencyService
{

    protected $em;
    protected $container;
    protected $request;
    private $cookieAndSessionName = "user-currency";
    private $cookieExpireInSec = 2592000; //month

    public function __construct(EntityManagerInterface $em, ContainerInterface $container, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
    }


    public function setCurrencyIfNotExist(Response $response, Currency $currency)
    {
        if (!$this->hasCurrency()) {
            $this->setCurrency($response, $currency);
        }
    }

    public function setCurrency(Response $response, Currency $currency): void
    {
        $this->setCookie($response, $currency);
        $this->setSession($currency);
    }

    public function getCurrency(): Currency
    {
        $currencyCode = $this->getRequest()->cookies->get($this->cookieAndSessionName);
        $currency = null;
        if ($currencyCode) {
            $currency = $this->em->getRepository(Currency::class)->findOneBy([
                "code" => $currencyCode,
                "deleted" => null,
            ]);
        }
        if ($currency == null) {
            $currency = $this->em->getRepository(Currency::class)->find(Currency::EGP);
        }

        return $currency;
    }

    public function hasCurrency(): bool
    {
        return $this->getRequest()->cookies->has($this->cookieAndSessionName);
    }



    private function setSession(Currency $currency)
    {
        $this->getRequest()->getSession()->set($this->cookieAndSessionName, $currency->getCode());
    }

    private function setCookie(Response $response, Currency $currency)
    {
        $response->headers
            ->setCookie(
                new Cookie(
                    $this->cookieAndSessionName,
                    $currency->getCode(),
                    time() + $this->cookieExpireInSec,
                    '/',
                    null,
                    false,
                    true
                )
            );
    }


    private function getRequest(): Request
    {
        return $this->request;
    }
}
