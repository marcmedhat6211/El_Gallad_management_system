<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\Category;

/**
 * AttributeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AttributeRepository extends \Doctrine\ORM\EntityRepository
{

    public function findByCategory(Category $category)
    {
        $search = new \stdClass();
        $search->deleted = 0;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->category = $category->getId();

        return $this->filter($search);
    }

    //    public function getAllProductWithOtherValuesInDropdownAndCheckBox(
    //        $count = false,
    //        $startLimit = null,
    //        $endLimit = null
    //    ) {
    //        $connection = $this->getEntityManager()->getConnection();
    //
    //        if ($count) {
    //            $sql = "SELECT COUNT( DISTINCT p.id) FROM product p "
    //                ."LEFT JOIN product_has_attribute pha ON p.id=pha.product_id "
    //                ."LEFT JOIN attribute a ON a.id=pha.attribute_id "
    //                ."WHERE p.deleted=0 and a.type in (3,4) AND pha.other_value IS NOT NULL ";
    //            $statement = $connection->prepare($sql);
    //            $statement->execute();
    //
    //            return $statement->fetchColumn();
    //        }
    //
    //
    //        $sql = "SELECT p.id FROM product p "
    //            ."LEFT JOIN product_has_attribute pha ON p.id=pha.product_id "
    //            ."LEFT JOIN attribute a ON a.id=pha.attribute_id "
    //            ."WHERE p.deleted=0 and a.type in (3,4) AND pha.other_value IS NOT NULL "
    //            ."GROUP BY p.id";
    //
    //        if ($startLimit !== null AND $endLimit !== null) {
    //            $sql .= " LIMIT ".$startLimit.", ".$endLimit;
    //        }
    //        $statement = $connection->prepare($sql);
    //        $statement->execute();
    //        $filterResult = $statement->fetchAll();
    //        $result = array();
    //        foreach ($filterResult as $key => $r) {
    //            $object = $this->getEntityManager()->getRepository('ProductBundle:Product')->find($r['id']);
    //            $object->minPrice = $this->getEntityManager()->getRepository('ProductBundle:Product')->getMinPrice($r['id']);
    //            $result[] = $object;
    //
    //        }
    //
    //        return $result;
    //    }


    private function getStatement()
    {
        return $this->createQueryBuilder('a');
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            "a.id",
            "a.tarteb",
            "a.title",
            "a.type",
            "a.search",
            "a.mandatory",
        ];

        if (isset($search->ordr) and \PN\Utils\Validate::not_null($search->ordr)) {
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
        if (isset($search->string) and \PN\Utils\Validate::not_null($search->string)) {
            $statement->andWhere('a.id LIKE :searchTerm '
                .'OR a.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%'.trim($search->string).'%');
        }

        if (isset($search->id) and $search->id > 0) {
            $statement->andWhere('a.id = :id');
            $statement->setParameter('id', $search->id);
        }
        if (isset($search->category) and $search->category > 0) {
            $statement->andWhere('a.category = :category');
            $statement->setParameter('category', $search->category);
        }


        if (isset($search->deleted) and in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('a.deleted IS NOT NULL');
            } else {
                $statement->andWhere('a.deleted IS NULL');
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
        $statement->select("COUNT(DISTINCT a.id)");
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

        $statement->groupBy('a.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        return $statement->getQuery()->execute();
    }
}
