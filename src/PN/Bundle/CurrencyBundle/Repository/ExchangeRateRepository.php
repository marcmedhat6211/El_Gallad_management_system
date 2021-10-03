<?php

namespace PN\Bundle\CurrencyBundle\Repository;

use PN\Bundle\CurrencyBundle\Entity\ExchangeRate;

/**
 * @method ExchangeRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExchangeRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExchangeRate[]    findAll()
 * @method ExchangeRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeRateRepository extends \Doctrine\ORM\EntityRepository
{

    public function getExchangeRate(
        $sourceCurrencyId,
        $targetCurrencyId,
        $shippingRate = false,
        $inEgypt = false
    ): float {
        if ($shippingRate == true and $inEgypt == true) {
            throw new \Exception("You can't get the shipping ratio and egypt ratio in same time");
        }

        $selectColumn = "e.ratio";
        if ($shippingRate == true) {
            $selectColumn = "e.shippingRatio";
        } elseif ($inEgypt == true) {
            $selectColumn = "e.ratioInEgypt";
        }

        $result = $this->createQueryBuilder('e')
            ->select($selectColumn.' AS ratio')
            ->andWhere('e.sourceCurrency = :sourceCurrencyId')
            ->andWhere('e.targetCurrency = :targetCurrencyId')
            ->setParameter('sourceCurrencyId', $sourceCurrencyId)
            ->setParameter('targetCurrencyId', $targetCurrencyId)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if ($result != null) {
            return ($result['ratio'] > 0) ? $result['ratio'] : 1;
        }

        return 1;
    }

//    @todo: need refactor
    public function updateProductPriceRate($sourceCurrencyId, $ratio)
    {
        if ($ratio == null or $ratio == 0) {
            $ratio = 1;
        }
        $connection = $this->getEntityManager()->getConnection();
        $sql = "UPDATE `product_price` SET `price`=`foreign_price`*:ratio,`promotional_price`=`foreign_promotional_price`*:ratio WHERE  `currency_id`=:sourceCurrencyId";
        $statement = $connection->prepare($sql);
        $statement->bindValue("sourceCurrencyId", $sourceCurrencyId);
        $statement->bindValue("ratio", $ratio);
        $statement->execute();
    }

//    @todo: need refactor
    public function updateCountryExtraKgPriceRate($ratio)
    {
        if ($ratio == null or $ratio == 0) {
            $ratio = 1;
        }
        $connection = $this->getEntityManager()->getConnection();
        $sql = "UPDATE `country` SET `extra_kg_price`=`extra_kg_foreign_price`*:ratio ";
        $statement = $connection->prepare($sql);
        $statement->bindValue("ratio", $ratio);
        $statement->execute();
    }

//    @todo: need refactor
    public function updateInternationalShippingPriceRate($ratio)
    {
        if ($ratio == null or $ratio == 0) {
            $ratio = 1;
        }
        $connection = $this->getEntityManager()->getConnection();
        $sql = "UPDATE `country_shipping_price` SET `price`=`foreign_price`*:ratio ";
        $statement = $connection->prepare($sql);
        $statement->bindValue("ratio", $ratio);
        $statement->execute();
    }
}
