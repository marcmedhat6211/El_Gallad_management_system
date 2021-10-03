<?php

namespace PN\Bundle\CurrencyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\BaseBundle\Service\UserCountryAndCurrencyService;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Entity\ExchangeRate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExchangeRateService
{

    protected $em;
    protected $container;
    private $userSiteCountry;
    private $exchangeRate;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function convertAmountUserCurrency(Currency $currency, $amount)
    {
        if ($this->userSiteCountry == null) {
            $this->userSiteCountry = $this->container->get(UserCountryAndCurrencyService::class)->getSiteCountry();
        }

        $exchangeRate = $this->getExchangeRate($currency, $this->userSiteCountry->getCurrency());

        return $exchangeRate * $amount;
    }

    public function getExchangeRate(Currency $sourceCurrency, Currency $targetCurrency)
    {
        if ($this->userSiteCountry == null) {
            $this->userSiteCountry = $this->container->get(UserCountryAndCurrencyService::class)->getSiteCountry();
        }
        if (!isset($this->exchangeRate[$sourceCurrency->getId()][$targetCurrency->getId()])) {
            $inEgypt = false;
            if ($this->userSiteCountry->getCountryCode() == "EG") {
                $inEgypt = true;
            }
            $exchangeRate = $this->em->getRepository(ExchangeRate::class)->getExchangeRate($sourceCurrency->getId(),
                $targetCurrency->getId(), false, $inEgypt);
            $this->exchangeRate[$sourceCurrency->getId()][$targetCurrency->getId()] = $exchangeRate;
        }

        return $this->exchangeRate[$sourceCurrency->getId()][$targetCurrency->getId()];
    }


}
