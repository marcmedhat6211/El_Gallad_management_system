<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use PN\Bundle\ProductBundle\Entity\Product;

class PrepareProductSearchIndexRepository extends EntityRepository
{

    public function insert(Product $product)
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "INSERT INTO prepare_product_search_index (product_id, created) VALUES (:productId, :created)";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue('productId', $product->getId());
        $stmt->bindValue('created', date("Y-m-d H:i:s"));
        $stmt->execute();
    }
}
