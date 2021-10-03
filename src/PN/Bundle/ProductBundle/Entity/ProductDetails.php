<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use http\Env\Request;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\MediaBundle\Entity\Document;

/**
 * @Gedmo\Loggable
 * @ORM\Table(name="product_details")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductRepository")
 */
class ProductDetails
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Product", inversedBy="details")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CurrencyBundle\Entity\Currency")
     */
    private $currency;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\MediaBundle\Entity\Document", cascade={"persist", "remove" })
     */
    private $tearSheet;


    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->tearSheet = null;
        }
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

//    /**
//     * @return Collection|Product[]
//     */
//    public function getRelatedProducts(): Collection
//    {
//        return $this->relatedProducts;
//    }
//
//    public function addRelatedProduct(Product $relatedProduct): self
//    {
//        if (!$this->relatedProducts->contains($relatedProduct)) {
//            $this->relatedProducts[] = $relatedProduct;
//        }
//
//        return $this;
//    }
//
//    public function removeRelatedProduct(Product $relatedProduct): self
//    {
//        $this->relatedProducts->removeElement($relatedProduct);
//
//        return $this;
//    }

    public function getTearSheet(): ?Document
    {
        return $this->tearSheet;
    }

    public function setTearSheet(?Document $tearSheet): self
    {
        $this->tearSheet = $tearSheet;

        return $this;
    }
}
