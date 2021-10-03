<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use PN\Bundle\ProductBundle\Entity\Occasion;

class ProductHasOccasionRepository extends EntityRepository
{

    public function removeProductByOccasion(Occasion $occasion)
    {
        return $this->createQueryBuilder("pho")
            ->delete()
            ->andWhere("pho.occasion = :occasionId")
            ->setParameter("occasionId", $occasion->getId())
            ->getQuery()->execute();
    }

}
