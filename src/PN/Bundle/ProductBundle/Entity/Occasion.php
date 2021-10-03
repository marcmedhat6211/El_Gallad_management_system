<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\ProductBundle\Entity\Translation\OccasionTranslation;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * Occasion
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("occasion")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\OccasionRepository")
 */
class Occasion implements Translatable, DateTimeInterface
{

    use VirtualDeleteTrait,
        DateTimeTrait,
        LocaleTrait;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\SeoBundle\Entity\Seo", inversedBy="occasion", cascade={"persist", "remove"})
     */
    private $seo;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ContentBundle\Entity\Post", inversedBy="occasion", cascade={"persist", "remove"})
     */
    private $post;

    /**
     * @ORM\Column(name="title", type="string", length=45)
     */
    private $title;

    /**
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = false;

    /**
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = true;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Translation\OccasionTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\ProductHasOccasion", mappedBy="occasion", cascade={"persist"})
     */
    private $productHasOccasions;

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->active = false;
            $this->seo = clone $this->seo;
            $this->post = clone $this->post;
            $translationsClone = new ArrayCollection();
            foreach ($this->getTranslations() as $translation) {
                $itemClone = clone $translation;
                $itemClone->setTranslatable($this);
                $translationsClone->add($itemClone);
            }
            $this->translations = $translationsClone;
        }
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->productHasOccasions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;

        return $this;
    }

    public function getSeo(): ?Seo
    {
        return $this->seo;
    }

    public function setSeo(?Seo $seo): self
    {
        $this->seo = $seo;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }


    /**
     * @return Collection|OccasionTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(OccasionTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(OccasionTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductHasOccasion[]
     */
    public function getProductHasOccasions(): Collection
    {
        return $this->productHasOccasions;
    }

    public function addProductHasOccasion(ProductHasOccasion $productHasOccasion): self
    {
        if (!$this->productHasOccasions->contains($productHasOccasion)) {
            $this->productHasOccasions[] = $productHasOccasion;
            $productHasOccasion->setOccasion($this);
        }

        return $this;
    }

    public function removeProductHasOccasion(ProductHasOccasion $productHasOccasion): self
    {
        if ($this->productHasOccasions->removeElement($productHasOccasion)) {
            // set the owning side to null (unless already changed)
            if ($productHasOccasion->getOccasion() === $this) {
                $productHasOccasion->setOccasion(null);
            }
        }

        return $this;
    }


}
