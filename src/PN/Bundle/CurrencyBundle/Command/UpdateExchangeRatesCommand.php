<?php

namespace PN\Bundle\CurrencyBundle\Command;

use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Entity\ExchangeRate;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateExchangeRatesCommand extends ContainerAwareCommand
{

    protected static $defaultName = 'app:update-exchange-rates';
    private $errorFactor = 1.05; // 5%
    private $currencies = [];
    private $currencyCodes = [];


    protected function configure()
    {
        $this->setDescription('Update Exchange rates from API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $currencies = $this->getCurrencies();

        $countyCodes = $this->getCountyCodes();
        foreach ($countyCodes as $code) {
            $sourceCurrencyCode = $code;
            $exchangeRates = $this->getExchangeRates($sourceCurrencyCode, $countyCodes);

            $sourceCurrency = $currencies[$sourceCurrencyCode];
            foreach ($exchangeRates as $apiCurrency => $apiRatio) {
                $targetCurrency = $currencies[$apiCurrency];
                $exchangeRate = $em->getRepository(ExchangeRate::class)->findOneBy([
                    "sourceCurrency" => $sourceCurrency,
                    "targetCurrency" => $targetCurrency,
                ]);

                if (!$exchangeRate) {
                    $exchangeRate = new ExchangeRate();
                    $exchangeRate->setSourceCurrency($sourceCurrency);
                    $exchangeRate->setTargetCurrency($targetCurrency);
                    $exchangeRate->setCreator("System");
                }
                $exchangeRate->setRatio(round($apiRatio * $this->errorFactor, 3));
                $exchangeRate->setModifiedBy("System");
                $em->persist($exchangeRate);
            }
            $em->flush();
        }
    }

    private function getCurrencies()
    {
        if (count($this->currencies) > 0) {
            return $this->currencies;
        }
        $em = $this->getContainer()->get('doctrine')->getManager();

        $currencies = $em->getRepository(Currency::class)->findAll();
        foreach ($currencies as $currency) {
            $this->currencies[$currency->getCode()] = $currency;
        }

        return $this->currencies;
    }

    private function getCountyCodes()
    {
        if (count($this->currencyCodes) > 0) {
            return $this->currencyCodes;
        }
        $currencies = $this->getCurrencies();
        foreach ($currencies as $currency) {
            $this->currencyCodes[] = $currency->getCode();
        }

        return $this->currencyCodes;
    }

    private function getExchangeRates($sourceCurrencyCode, array $targetCurrencyCodes): array
    {
        $returnValues = [];
        $exchangeRates = $this->callExchangeAPI($sourceCurrencyCode, $targetCurrencyCodes);
        foreach ($targetCurrencyCodes as $code) {
            $key = $sourceCurrencyCode.$code;
            if (array_key_exists($key, $exchangeRates)) {
                $returnValues[$code] = $exchangeRates[$key];

            } else {
                $returnValues[$code] = 1;
            }
        }

        return $returnValues;
    }

    private function callExchangeAPI($sourceCurrencyCode, array $targetCurrencyCodes)
    {
        // set API Endpoint and API key
        $access_key = '80344900c71a10c2da647db031daf389';

        $data = [
            "access_key" => $access_key,
            "source" => $sourceCurrencyCode,
            "currencies" => implode($targetCurrencyCodes, ","),

        ];
        $url = 'https://apilayer.net/api/live';
        $response = $this->get($url, $data);

        // Decode JSON response:
        $exchangeRates = json_decode($response, true);

        // Access the exchange rate values, e.g. GBP:
        return $exchangeRates['quotes'];
    }

    public function get($url, $data = false)
    {

        if ($data) {
            $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}
