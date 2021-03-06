<?php

namespace PN\Bundle\CurrencyBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Peter Nassef <peter.nassef@gmail.com>
 * @version 1.0
 */
class VarsExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('exchangeRateWithMoneyFormat', [VarsRuntime::class, 'exchangeRateWithMoneyFormat']),
            new TwigFilter('moneyFormat', [VarsRuntime::class, 'moneyFormat']),
        ];
    }
    public function getFunctions()
    {
        return [
            new TwigFunction('userCurrency', [VarsRuntime::class, 'getUserCurrency']),
            new TwigFunction('userCurrencySymbol', [VarsRuntime::class, 'getUserCurrencySymbol']),
        ];
    }

    public function getName()
    {
        return 'currency.extension';
    }

}
