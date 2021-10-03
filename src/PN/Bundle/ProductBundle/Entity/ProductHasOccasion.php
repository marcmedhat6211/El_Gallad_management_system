<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\UserBundle\Entity\User;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("product_occasion")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductHasOccasionRepository")
 */
class ProductHasOccasion {

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Occasion", inversedBy="productHasOccasions")
     */
    protected $occasion;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productHasOccasions")
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
    public function updatedTimestamps() {
        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
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

    public function getOccasion(): ?Occasion
    {
        return $this->occasion;
    }

    public function setOccasion(?Occasion $occasion): self
    {
        $this->occasion = $occasion;

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
