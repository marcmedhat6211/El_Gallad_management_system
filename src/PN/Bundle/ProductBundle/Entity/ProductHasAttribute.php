<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductHasAttribute
 * @ORM\Table(name="product_has_attribute")
 * * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductHasAttributeRepository")
 */
class ProductHasAttribute {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productHasAttributes")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="productHasAttributes")
     */
    private $attribute;

    /**
     * @ORM\ManyToOne(targetEntity="SubAttribute", inversedBy="productHasAttributes")
     */
    private $subAttribute;

    /**
     * @var string
     *
     * @ORM\Column(name="other_value", type="string", length=255, nullable=true)
     */
    private $otherValue;

    public function __clone() {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOtherValue(): ?string
    {
        return $this->otherValue;
    }

    public function setOtherValue(?string $otherValue): self
    {
        $this->otherValue = $otherValue;

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

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getSubAttribute(): ?SubAttribute
    {
        return $this->subAttribute;
    }

    public function setSubAttribute(?SubAttribute $subAttribute): self
    {
        $this->subAttribute = $subAttribute;

        return $this;
    }

}
