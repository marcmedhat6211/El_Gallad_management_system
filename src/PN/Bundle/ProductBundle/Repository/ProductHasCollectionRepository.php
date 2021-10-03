<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PN\Bundle\ProductBundle\Entity\Collection;
use PN\Utils\Validate;

class ProductHasCollectionRepository extends EntityRepository
{
    public function removeProductByCollection(Collection $collection)
    {
        return $this->createQueryBuilder("phc")
            ->delete()
            ->andWhere("phc.collection = :collectionId")
            ->setParameter("collectionId", $collection->getId())
            ->getQuery()->execute();
    }

}
