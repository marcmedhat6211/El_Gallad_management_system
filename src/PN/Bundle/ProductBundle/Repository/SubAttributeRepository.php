<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\ServiceBundle\Utils\Validate;

/**
 * @method SubAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubAttribute[]    findAll()
 * @method SubAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubAttributeRepository extends \Doctrine\ORM\EntityRepository
{

    private function getStatement()
    {
        return $this->createQueryBuilder('sa');
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            "sa.id",
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
            $statement->andWhere('sa.id LIKE :searchTerm '
                .'OR sa.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%'.trim($search->string).'%');
        }

        if (isset($search->id) and $search->id > 0) {
            $statement->andWhere('sa.id = :id');
            $statement->setParameter('id', $search->id);
        }

        if (isset($search->ids) and is_array($search->ids) and count($search->ids) > 0) {
            $statement->andWhere('sa.id IN (:ids)');
            $statement->setParameter('ids', $search->ids);
        }
        if (isset($search->attribute) and $search->attribute > 0) {
            $statement->andWhere('sa.attribute = :attribute');
            $statement->setParameter('attribute', $search->attribute);
        }


        if (isset($search->deleted) and in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('sa.deleted IS NOT NULL');
            } else {
                $statement->andWhere('sa.deleted IS NULL');
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
        $statement->select("COUNT(DISTINCT sa.id)");
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

        $statement->groupBy('sa.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        return $statement->getQuery()->execute();
    }
}
