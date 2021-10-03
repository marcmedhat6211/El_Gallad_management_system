<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use PN\ServiceBundle\Utils\Validate;

/**
 * @method ProductPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductPrice[]    findAll()
 * @method ProductPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPriceRepository extends \Doctrine\ORM\EntityRepository
{

    private function getStatement()
    {
        return $this->createQueryBuilder('pp');
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            "pp.id",
            "pp.minQuantity",
            "pp.maxQuantity",
            "pp.product",
            "pp.unitPrice",
            "pp.promotionalPrice",
            "pp.promotionalExpiryDate",
        ];

        if (isset($search->ordr) and Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) and array_key_exists($columnNumber, $sortSQL)) {
                $statement->addOrderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            $statement->addOrderBy($sortSQL[0]);
        }
    }

    private function filterWhereClause(QueryBuilder $statement, \stdClass $search)
    {
        if (isset($search->string) and Validate::not_null($search->string)) {
            $statement->andWhere('pp.id LIKE :searchTerm '
                .'OR pp.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%'.trim($search->string).'%');
        }

        if (isset($search->id) and $search->id > 0) {
            $statement->andWhere('pp.id = :id');
            $statement->setParameter('id', $search->id);
        }

        if (isset($search->qty) and $search->qty > 0) {
            $statement->andWhere(':qty BETWEEN pp.minQuantity AND pp.maxQuantity');
            $statement->setParameter('qty', $search->qty);
        }

        if (isset($search->ids) and is_array($search->ids) and count($search->ids) > 0) {
            $statement->andWhere('pp.id IN (:ids)');
            $statement->setParameter('ids', $search->ids);
        }

        if (isset($search->product) and $search->product > 0) {
            $statement->andWhere('pp.product = :product');
            $statement->setParameter('product', $search->product);
        }

        if (isset($search->deleted) and in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('pp.deleted IS NOT NULL');
            } else {
                $statement->andWhere('pp.deleted IS NULL');
            }
        }
    }

    private function filterPagination(QueryBuilder $statement, $startLimit = null, $endLimit = null)
    {
        if ($startLimit === null or $endLimit === null) {
            return false;
        }
        $statement->setFirstResult($startLimit)
            ->setMaxResults($endLimit);
    }

    private function filterCount(QueryBuilder $statement)
    {
        $statement->select("COUNT(DISTINCT pp.id)");
        $statement->setMaxResults(1);

        $count = $statement->getQuery()->getOneOrNullResult();
        if (is_array($count) and count($count) > 0) {
            return (int)reset($count);
        }

        return 0;
    }

    public function filter($search, $count = false, $startLimit = null, $endLimit = null)
    {
        $statement = $this->getStatement();
        $this->filterWhereClause($statement, $search);

        if ($count == true) {
            return $this->filterCount($statement);
        }

        $statement->groupBy('pp.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        return $statement->getQuery()->execute();
    }

    public function getMaxAndMinPrices() {
        $statement = $this->createQueryBuilder('pp')
            ->select('COALESCE(MAX(pp.promotionalPrice), MAX(pp.unitPrice)) AS max_price, COALESCE(MIN(pp.promotionalPrice), MIN(pp.unitPrice)) AS min_price')
            ->where('pp.product IS NOT NULL')
            ->andWhere('pp.deleted IS NULL')
            ;

        $result = $statement->getQuery()->execute();
        return $result ? $result[0] : null;
    }
}
