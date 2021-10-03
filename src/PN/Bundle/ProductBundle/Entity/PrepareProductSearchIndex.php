<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("prepare_product_search_index")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\PrepareProductSearchIndexRepository")
 */
class PrepareProductSearchIndex {


    /**
     * @ORM\Id
     * @ORM\Column(name="product_id", type="integer")
     */
    protected $product;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps() {
        $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
    }


    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return PrepareProductSearchIndex
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set product
     *
     * @param \PN\Bundle\ProductBundle\Entity\Product $product
     *
     * @return PrepareProductSearchIndex
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \PN\Bundle\ProductBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
