<?php

namespace PN\Bundle\CurrencyBundle\Controller\Administration;

use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Entity\ExchangeRate;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExchangeRateController extends Controller
{

    private $currencies = [];

    /**
     * @Route("/exchange/rate", name="exchange_rate", methods={"GET", "POST"})
     */
    public function indexAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $this->currencies = $em->getRepository(Currency::class)->findAll();

        if ($request->isMethod('POST')) {
            $this->save($request);

            return $this->redirectToRoute('exchange_rate');
        }

        return $this->render('currency/admin/exchangeRate/index.html.twig', [
            'currencies' => $this->currencies,
            'exchangeRates' => $this->getExchangeRateInArray(),
        ]);
    }

    private function getExchangeRateInArray()
    {
        $em = $this->getDoctrine()->getManager();

        $exchangeRates = $em->getRepository(ExchangeRate::class)->findAll();
        $exchangeRatesArr = [];
        foreach ($exchangeRates as $exchangeRate) {
            $exchangeRatesArr[$exchangeRate->getSourceCurrency()->getId()][$exchangeRate->getTargetCurrency()->getId()] = [
                "ratio" => $exchangeRate->getRatio(),
                "shippingRatio" => $exchangeRate->getShippingRatio(),
                "ratioInEgypt" => $exchangeRate->getRatioInEgypt(),
                "modified" => $exchangeRate->getModified(),
                "modifiedBy" => $exchangeRate->getModifiedBy(),
            ];
        }

        return $exchangeRatesArr;
    }

    private function save(Request $request)
    {
        $ratio = $request->request->get('ratio');
        $shippingRatio = $request->request->get('shippingRatio');
        $egyptRatio = $request->request->get('egyptRatio');

        foreach ($ratio as $sourceCurrencyId => $data) {
            foreach ($data as $targetCurrencyId => $value) {
                if (!Validate::not_null($value)) {
                    continue;
                }
                $shippingRatioItem = null;
                if (isset($shippingRatio[$sourceCurrencyId][$targetCurrencyId])) {
                    $shippingRatioItem = $shippingRatio[$sourceCurrencyId][$targetCurrencyId];
                }
                $egyptRatioItem = null;
                if (isset($egyptRatio[$sourceCurrencyId][$targetCurrencyId])) {
                    $egyptRatioItem = $egyptRatio[$sourceCurrencyId][$targetCurrencyId];
                }

                $this->addOrUpdateExchangeRate($sourceCurrencyId, $targetCurrencyId, $value, $shippingRatioItem,
                    $egyptRatioItem);
            }
        }
        $this->addFlash("success", "Saved successfully");
    }

    private function addOrUpdateExchangeRate(
        $sourceCurrencyId,
        $targetCurrencyId,
        $ratio,
        $shippingRatio,
        $ratioInEgypt
    ) {
        $em = $this->getDoctrine()->getManager();

        $exchangeRate = $em->getRepository(ExchangeRate::class)->findOneBy([
            "sourceCurrency" => $sourceCurrencyId,
            "targetCurrency" => $targetCurrencyId,
        ]);

        if (!Validate::not_null($ratio) and !Validate::not_null($shippingRatio) and $exchangeRate != null) {
            $em->remove($exchangeRate);

            return;
        }

        $userName = $this->get('user')->getUserName();
        if (!$exchangeRate) {
            $sourceCurrency = $this->getCurrencyById($sourceCurrencyId);
            $targetCurrency = $this->getCurrencyById($targetCurrencyId);
            $exchangeRate = new ExchangeRate();
            $exchangeRate->setSourceCurrency($sourceCurrency);
            $exchangeRate->setTargetCurrency($targetCurrency);
            $exchangeRate->setCreator($userName);
        }
        $exchangeRate->setModifiedBy($userName);
        $exchangeRate->setRatio($ratio);
        $exchangeRate->setShippingRatio($shippingRatio);
        $exchangeRate->setRatioInEgypt($ratioInEgypt);
        $em->persist($exchangeRate);
        $em->flush();

        if ($exchangeRate->getTargetCurrency()->getId() == Currency::EGP) {
            //update productPrice
            $em->getRepository(ExchangeRate::class)->updateProductPriceRate($exchangeRate->getSourceCurrency()->getId(),
                $exchangeRate->getRatio());

            if ($exchangeRate->getSourceCurrency()->getId() == Currency::USD) {
//              @todo: Remove this 2 lines after finish new Shipping Bundle
                $em->getRepository(ExchangeRate::class)->updateCountryExtraKgPriceRate($exchangeRate->getShippingRatio());
                $em->getRepository(ExchangeRate::class)->updateInternationalShippingPriceRate($exchangeRate->getShippingRatio());
            }
        }

    }

    /**
     * Optimization
     * Load Currency from private property instade of reload currency from database
     * @param $currencyId
     * @return Currency|null
     */
    private function getCurrencyById($currencyId): ?Currency
    {
        $currentCurrency = null;
        foreach ($this->currencies as $currency) {
            if ($currencyId == $currency->getId()) {
                $currentCurrency = $currency;
                break;
            }
        }

        return $currentCurrency;
    }
}
