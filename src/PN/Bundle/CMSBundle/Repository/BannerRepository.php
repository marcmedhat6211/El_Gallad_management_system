<?php

namespace PN\Bundle\CMSBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use PN\Utils\SQL;
use PN\Utils\Validate;

/**
 * BannerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BannerRepository extends \Doctrine\ORM\EntityRepository {

    private function getStatement()
    {
        return $this->createQueryBuilder('b');
    }

    private function filterWhereClause(QueryBuilder $statement, \stdClass $search)
    {
        if (isset($search->string) AND \PN\ServiceBundle\Utils\Validate::not_null($search->string)) {
            $statement->andWhere('b.id LIKE :searchTerm '
                .'OR b.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%'.trim($search->string).'%');
        }

        if (isset($search->title) AND $search->title != "") {
            $statement->andWhere('b.title = :title');
            $statement->setParameter('title', $search->title);
        }

        if (isset($search->publish) and $search->publish != "") {
            $statement->andWhere('b.publish = :publish');
            $statement->setParameter('publish', $search->publish);
        }

        if (isset($search->placement) AND $search->placement != "") {
            $statement->andWhere('b.placement = :placement');
            $statement->setParameter('placement', $search->placement);
        }

        if (isset($search->subTitle) AND $search->subTitle != "") {
            $statement->andWhere('b.subTitle = :subTitle');
            $statement->setParameter('subTitle', $search->subTitle);
        }
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            'b.id',
            'b.title',
            'b.tarteb',
        ];

        if (isset($search->ordr) AND Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) AND array_key_exists($columnNumber, $sortSQL)) {
                $statement->orderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            $statement->orderBy($sortSQL[0], "DESC");
        }
    }

    private function filterPagination(QueryBuilder $statement, $startLimit = null, $endLimit = null)
    {
        if ($startLimit === null OR $endLimit === null) {
            return false;
        }
        $statement->setFirstResult($startLimit)
            ->setMaxResults($endLimit);
    }

    private function filterCount(QueryBuilder $statement)
    {
        $statement->select("COUNT(DISTINCT b.id)");
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

        $statement->groupBy('b.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        return $statement->getQuery()->execute();
    }
}
