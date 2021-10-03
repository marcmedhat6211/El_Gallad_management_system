<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\UserBundle\Entity\User;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("product_collection")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductHasCollectionRepository")
 */
class ProductHasCollection
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Collection", inversedBy="productHasCollections")
     */
    protected $collection;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productHasCollections")
     */
    protected $product;


    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }


    public function __clone()
    {
        $this->collection = null;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

}
