<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="product_prices")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductPriceRepository")
 * @Gedmo\Loggable
 */
class ProductPrice implements DateTimeInterface
{

    use VirtualDeleteTrait,
        DateTimeTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="min_quantity", type="integer")
     */
    private $minQuantity;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="max_quantity", type="integer")
     */
    private $maxQuantity;

    /**
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     * @ORM\Column(name="unit_price", type="float")
     */
    private $unitPrice;

    /**
     * @Gedmo\Versioned
     * @Assert\NotBlank()
     * @ORM\Column(name="promotional_price", type="float", nullable=true)
     */
    private $promotionalPrice;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="promotional_expiry_date", type="date", nullable=true)
     */
    private $promotionalExpiryDate;

    /**
     * @ORM\ManyToOne(targetEntity="PN\Bundle\ProductBundle\Entity\Product", inversedBy="productPrices", cascade={"remove"})
     */
    private $product;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setModified(new \DateTime(date('Y-m-d H:i:s')));

        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function getSellPrice(): ?float
    {
        $sellPrice = $this->getUnitPrice();

        $promotionalExpiryDate = $this->getPromotionalExpiryDate();
        if ($promotionalExpiryDate) {
            $currentDate = new \DateTime();
            if ($promotionalExpiryDate->format("Y-m-d") > $currentDate->format("Y-m-d")) {
                $sellPrice = $this->getPromotionalPrice();
            }
        }

        return $sellPrice;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(int $minQuantity): self
    {
        $this->minQuantity = $minQuantity;

        return $this;
    }

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(int $maxQuantity): self
    {
        $this->maxQuantity = $maxQuantity;

        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getPromotionalPrice(): ?float
    {
        return $this->promotionalPrice;
    }

    public function setPromotionalPrice(?float $promotionalPrice): self
    {
        $this->promotionalPrice = $promotionalPrice;

        return $this;
    }

    public function getPromotionalExpiryDate(): ?\DateTime
    {
        return $this->promotionalExpiryDate;
    }

    public function setPromotionalExpiryDate(?\DateTime $promotionalExpiryDate): self
    {
        $this->promotionalExpiryDate = $promotionalExpiryDate;

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
