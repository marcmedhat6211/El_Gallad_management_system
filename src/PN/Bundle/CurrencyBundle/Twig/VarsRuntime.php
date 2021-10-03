<?php

namespace PN\Bundle\CurrencyBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\BaseBundle\Service\UserCountryAndCurrencyService;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Service\ExchangeRateService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Peter Nassef <peter.nassef@gmail.com>
 * @version 1.0
 */
class VarsRuntime implements RuntimeExtensionInterface
{

    private $container;
    private $em;
    private $userCurrency = null;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function getUserCurrencySymbol()
    {
        $userCurrency = $this->getUserCurrency();

        return $userCurrency->getSymbol();
    }

    public function exchangeRateWithMoneyFormat($amount, Currency $currency, $withCurrency = true)
    {
        $convertedAmount = $this->container->get(ExchangeRateService::class)->convertAmountUserCurrency($currency,
            $amount);

        return $this->moneyFormat($convertedAmount,  $withCurrency);
    }

    public function moneyFormat($amount, $withCurrency = true)
    {
        $userCurrency = $this->getUserCurrency();
        $numberDecimals = 0;
        if ($userCurrency->getId() != Currency::EGP) {
            $numberDecimals = 2;
        }
        $returnValue = number_format($amount, $numberDecimals);
        if ($withCurrency == true) {
            if ($userCurrency->getId() == Currency::EGP) {

                $returnValue .= " ".$userCurrency->getSymbol();
            } else {
                $returnValue = $userCurrency->getSymbol()." ".$returnValue;
            }
        }

        return $returnValue;
    }

    public function getUserCurrency()
    {
        if ($this->userCurrency == null) {
            $userSiteCountry = $this->container->get(UserCountryAndCurrencyService::class)->getSiteCountry();
            $this->userCurrency = $userSiteCountry->getCurrency();
        }

        return $this->userCurrency;
    }
}
