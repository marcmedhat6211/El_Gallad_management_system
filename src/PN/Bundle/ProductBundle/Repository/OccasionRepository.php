<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\Occasion;

/**
 * @method Occasion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Occasion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccasionRepository extends EntityRepository
{
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $search = new \stdClass();
        $search->id = $id;
        $search->deleted = 0;
        $result = $this->filter($search);

        return reset($result);
    }

    public function clearAllActive()
    {
        return $this->createQueryBuilder("o")
            ->update()
            ->set("o.active", "0")
            ->getQuery()
            ->execute();
    }

    public function findAll()
    {
        return $this->findBy(array('deleted' => null));
    }

    private function getStatement()
    {
        return $this->createQueryBuilder('o')
            ->addSelect("COUNT(pho.occasion) AS noOfProducts")
            ->leftJoin("o.productHasOccasions", "pho");
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            "o.id",
            "o.title",
            "o.startDate",
            "o.endDate",
            "o.active",
        ];

        if (isset($search->ordr) and \PN\Utils\Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) and array_key_exists($columnNumber, $sortSQL)) {
                $statement->addOrderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            $statement->addOrderBy($sortSQL[1]);
        }
    }

    private function filterWhereClause(QueryBuilder $statement, \stdClass $search)
    {
        if (isset($search->string) and \PN\Utils\Validate::not_null($search->string)) {
            $statement->andWhere('o.id LIKE :searchTerm '
                . 'OR o.title LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%' . trim($search->string) . '%');
        }

        if (isset($search->id) and $search->id > 0) {
            $statement->andWhere('o.id = :id');
            $statement->setParameter('id', $search->id);
        }
        if (isset($search->publish) and (is_bool($search->publish) or in_array($search->publish, [0, 1]))) {
            $statement->andWhere('o.publish = :publish');
            $statement->setParameter('publish', $search->publish);
        }
        if (isset($search->active) and (is_bool($search->active) or in_array($search->active, [0, 1]))) {
            $statement->andWhere('o.active = :active');
            $statement->setParameter('active', $search->active);
        }


        if (isset($search->deleted) and in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('o.deleted IS NOT NULL');
            } else {
                $statement->andWhere('o.deleted IS NULL');
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
        $statement->select("COUNT(DISTINCT o.id)");
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

        $statement->groupBy('o.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        $rows = $statement->getQuery()->execute();
        $entities = [];
        foreach ($rows as $row) {
            $entity = $row[0];
            $entity->noOfProducts = $row['noOfProducts'];
            $entities[] = $entity;

        }

        return $entities;
    }
}
