<?php

namespace PN\Bundle\CMSBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\ServiceBundle\Utils\SQL;
use PN\ServiceBundle\Utils\Validate;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends \Doctrine\ORM\EntityRepository {

    public function find($id, $lockMode = null, $lockVersion = null) {
        $statement = $this->getStatement();
        $statement->where("p.id=:id");
        $statement->setParameter("id", $id);
        return $statement->getQuery()->getOneOrNullResult();
    }




    private function getStatement() {
        return $this->createQueryBuilder('p');
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search) {
        $sortSQL = [
            'p.id',
            'p.tarteb',
            'p.title',
            'p.publish',
            'p.created'
        ];

        if (isset($search->ordr) and Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) and array_key_exists($columnNumber, $sortSQL)) {
                $statement->addOrderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            $statement->addOrderBy($sortSQL[1]);
        }
    }

    private function filterWhereClause(QueryBuilder $statement, \stdClass $search) {
        if (isset($search->string) and Validate::not_null($search->string)) {
            $statement->andWhere('p.id LIKE :searchTerm '
                . 'OR p.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%' . trim($search->string) . '%');
        }

        if (isset($search->ids) and is_array($search->ids) and count($search->ids) > 0) {
            $statement->andWhere('p.id in (:ids)');
            $statement->setParameter('ids', $search->ids);
        }

        if (isset($search->publish) AND $search->publish != "") {
            $statement->andWhere('p.publish = :publish');
            $statement->setParameter('publish', $search->publish);
        }


        if (isset($search->deleted) and in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('p.deleted IS NOT NULL');
            } else {
                $statement->andWhere('p.deleted IS NULL');
            }
        }
    }

    private function filterPagination(QueryBuilder $statement, $startLimit = NULL, $endLimit = NULL) {
        if ($startLimit === NULL or $endLimit === NULL) {
            return false;
        }
        $statement->setFirstResult($startLimit)
            ->setMaxResults($endLimit);
    }

    private function filterCount(QueryBuilder $statement) {
        $statement->select("COUNT(DISTINCT p.id)");
        $statement->setMaxResults(1);

        $count = $statement->getQuery()->getOneOrNullResult();
        if (is_array($count) and count($count) > 0) {
            return (int)reset($count);
        }
        return 0;
    }

    public function filter($search, $count = FALSE, $startLimit = NULL, $endLimit = NULL) {
        $statement = $this->getStatement();
        $this->filterWhereClause($statement, $search);

        if ($count == true) {
            return $this->filterCount($statement);
        }

        $statement->groupBy('p.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);
        return $statement->getQuery()->execute();
    }
}