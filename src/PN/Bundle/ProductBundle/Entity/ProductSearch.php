<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CurrencyBundle\Entity\Currency;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("product_search", indexes={@ORM\Index(columns={"normalized_text"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductSearchRepository")
 */
class ProductSearch
{

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Product",inversedBy="search")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CurrencyBundle\Entity\Currency")
     */
    private $currency;

    /**
     * @ORM\Column(name="specs", type="json", nullable=true)
     */
    private $specs;

    /**
     * @ORM\Column(name="min_price", type="float")
     */
    private $minPrice;

    /**
     * @ORM\Column(name="max_price", type="float")
     */
    private $maxPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="normalized_text", type="text", nullable=true)
     */
    private $normalizedTxt;

    /**
     * @ORM\Column(name="featured", type="boolean")
     */
    private $featured = false;

    /**
     * @ORM\Column(name="recommended_sort", type="smallint")
     */
    private $recommendedSort = 0;

    /**
     * @ORM\Column(name="new_arrival", type="boolean")
     */
    private $newArrival = false;

    /**
     * @ORM\Column(name="has_offer", type="boolean")
     */
    private $hasOffer = false;

    /**
     * @var string
     *
     * @ORM\Column(name="offer_expiry_date", type="date", nullable=true)
     */
    private $offerExpiryDate;

    /**
     * @ORM\Column(name="promotion_percentage", type="float", nullable=true)
     */
    private $promotionPercentage = 0;

    /**
     * @ORM\Column(name="last_update_datetime", type="datetime")
     */
    private $lastUpdate;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setLastUpdate(new \DateTime(date('Y-m-d H:i:s')));
        $this->calculateRecommendedSort();
    }

    private function calculateRecommendedSort()
    {
        $score = 0;
        if ($this->getFeatured()) {
            $score += 1;
        }
        $this->setRecommendedSort($score);
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function setCategory(?int $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSpecs(): ?array
    {
        return $this->specs;
    }

    public function setSpecs(?array $specs): self
    {
        $this->specs = $specs;

        return $this;
    }

    public function getNormalizedTxt(): ?string
    {
        return $this->normalizedTxt;
    }

    public function setNormalizedTxt(?string $normalizedTxt): self
    {
        $this->normalizedTxt = $normalizedTxt;

        return $this;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getHasOffer(): ?bool
    {
        return $this->hasOffer;
    }

    public function setHasOffer(bool $hasOffer): self
    {
        $this->hasOffer = $hasOffer;

        return $this;
    }

    public function getOfferExpiryDate(): ?\DateTimeInterface
    {
        return $this->offerExpiryDate;
    }

    public function setOfferExpiryDate(?\DateTimeInterface $offerExpiryDate): self
    {
        $this->offerExpiryDate = $offerExpiryDate;

        return $this;
    }

    public function getPromotionPercentage(): ?float
    {
        return $this->promotionPercentage;
    }

    public function setPromotionPercentage(float $promotionPercentage): self
    {
        $this->promotionPercentage = $promotionPercentage;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->lastUpdate;
    }

    public function setLastUpdate(\DateTimeInterface $lastUpdate): self
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
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

    public function getNewArrival(): ?bool
    {
        return $this->newArrival;
    }

    public function setNewArrival(bool $newArrival): self
    {
        $this->newArrival = $newArrival;

        return $this;
    }

    public function getRecommendedSort(): ?int
    {
        return $this->recommendedSort;
    }

    public function setRecommendedSort(int $recommendedSort): self
    {
        $this->recommendedSort = $recommendedSort;

        return $this;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function setMinPrice(float $minPrice): self
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(float $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }
}
