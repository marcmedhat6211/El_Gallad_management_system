<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PN\Bundle\CMSBundle\Entity\Project;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\MediaBundle\Entity\Document;
use PN\Bundle\ProductBundle\Entity\Translation\ProductTranslation;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Interfaces\UUIDInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\UuidTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use PN\Utils\SearchText;
use Symfony\Component\Validator\Constraints as Assert;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"normalized_text"}, flags={"fulltext"})})
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\ProductRepository")
 * @Gedmo\Loggable
 */
class Product implements Translatable, DateTimeInterface, UUIDInterface
{

    use VirtualDeleteTrait,
        DateTimeTrait,
        LocaleTrait,
        UuidTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\SeoBundle\Entity\Seo", inversedBy="product", cascade={"persist", "remove" }, fetch="EAGER")
     */
    private $seo;

    /**
     * Short Description
     * Description
     * Gallery
     *
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ContentBundle\Entity\Post", inversedBy="product", cascade={"persist", "remove" }, fetch="EAGER")
     */
    private $post;

    /**
     * @Assert\NotNull
     * @Gedmo\Versioned
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products", cascade={"persist"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Project", mappedBy="products")
     */
    private $projects;

    /**
     * @Assert\NotNull
     * @Gedmo\Versioned
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="sku", type="string", length=100, nullable=true)
     */
    private $sku;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="serial", type="string")
     */
    private $serial;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    private $tarteb;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="tag", type="string", length=100, nullable=true)
     */
    private $tag;

    /**
     * @ORM\Column(name="normalized_text", type="text", nullable=true)
     */
    protected $normalizedTxt;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="featured", type="boolean")
     */
    private $featured = false;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="new_arrival", type="boolean")
     */
    private $newArrival = false;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = true;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Translation\ProductTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    private $translations;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\ProductBundle\Entity\ProductDetails", mappedBy="product", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     */
    private $details;

    /**
     * @ORM\OneToOne(targetEntity="ProductSearch", mappedBy="product",cascade={"all"}, fetch="LAZY")
     */
    private $search;

    /**
     * @ORM\OneToMany(targetEntity="ProductHasAttribute", mappedBy="product", cascade={"persist"})
     */
    private $productHasAttributes;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\ProductHasCollection", mappedBy="product", cascade={"persist"})
     */
    private $productHasCollections;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\ProductHasOccasion", mappedBy="product", cascade={"persist"})
     */
    private $productHasOccasions;

    /**
     * @ORM\OneToMany(targetEntity="Attribute", mappedBy="product")
     */
    private $attributes;

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getObj(): array
    {
        return [
            "title" => (string)$this->getTitle(),
            "newArrival" => (bool)$this->getNewArrival(),
            "featured" => (bool)$this->getFeatured(),
        ];
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->productHasAttributes = new ArrayCollection();
        $this->productHasCollections = new ArrayCollection();
        $this->productHasOccasions = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->updateNormalizedTxt();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->seo = clone $this->seo;
            $this->post = clone $this->post;
            $details = clone $this->details;
            $details->setProduct($this);
            $this->details = $details;
            $translationsClone = new ArrayCollection();
            foreach ($this->getTranslations() as $translation) {
                $itemClone = clone $translation;
                $itemClone->setTranslatable($this);
                $translationsClone->add($itemClone);
            }
            $this->translations = $translationsClone;
            $specsClone = new ArrayCollection();
            foreach ($this->getProductHasAttributes() as $productHasAttribute) {
                $itemClone = clone $productHasAttribute;
                $itemClone->setProduct($this);
                $specsClone->add($itemClone);
            }
            $this->productHasAttributes = $specsClone;
        }
    }

    private function updateNormalizedTxt()
    {
        $keywords = [
            $this->getTitle(),
            $this->getSku(),
            $this->getTag(),
        ];
        foreach ($this->getTranslations() as $translation) {
            $keywords[] = $translation->getTitle();
        }

        $searchableKeyword = SearchText::makeSearchableKeywords($keywords);
        $this->setNormalizedTxt($searchableKeyword);
    }

    public function getProductHasAttributesByAttributeId(Attribute $attribute): Collection
    {
        return $this->productHasAttributes->filter(function ($entity) use ($attribute) {
            if ($entity->getAttribute()->getId() == $attribute->getId()) {
                return $entity;
            }
        });
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return !$this->currentTranslation ? $this->title : $this->currentTranslation->getTitle();
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getTarteb(): ?int
    {
        return $this->tarteb;
    }

    public function setTarteb(?int $tarteb): self
    {
        $this->tarteb = $tarteb;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

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

    public function getNewArrival(): ?bool
    {
        return $this->newArrival;
    }

    public function setNewArrival(bool $newArrival): self
    {
        $this->newArrival = $newArrival;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|ProductTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProductTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(ProductTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getDetails(): ?ProductDetails
    {
        return $this->details;
    }

    public function setDetails(?ProductDetails $details): self
    {
        // unset the owning side of the relation if necessary
        if ($details === null && $this->details !== null) {
            $this->details->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if ($details !== null && $details->getProduct() !== $this) {
            $details->setProduct($this);
        }

        $this->details = $details;

        return $this;
    }

    public function getSearch(): ?ProductSearch
    {
        return $this->search;
    }

    public function setSearch(?ProductSearch $search): self
    {
        // unset the owning side of the relation if necessary
        if ($search === null && $this->search !== null) {
            $this->search->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if ($search !== null && $search->getProduct() !== $this) {
            $search->setProduct($this);
        }

        $this->search = $search;

        return $this;
    }

    /**
     * @return Collection|ProductHasAttribute[]
     */
    public function getProductHasAttributes(): Collection
    {
        return $this->productHasAttributes;
    }

    public function addProductHasAttribute(ProductHasAttribute $productHasAttribute): self
    {
        if (!$this->productHasAttributes->contains($productHasAttribute)) {
            $this->productHasAttributes[] = $productHasAttribute;
            $productHasAttribute->setProduct($this);
        }

        return $this;
    }

    public function removeProductHasAttribute(ProductHasAttribute $productHasAttribute): self
    {
        if ($this->productHasAttributes->removeElement($productHasAttribute)) {
            // set the owning side to null (unless already changed)
            if ($productHasAttribute->getProduct() === $this) {
                $productHasAttribute->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductHasCollection[]
     */
    public function getProductHasCollections(): Collection
    {
        return $this->productHasCollections;
    }

    public function addProductHasCollection(ProductHasCollection $productHasCollection): self
    {
        if (!$this->productHasCollections->contains($productHasCollection)) {
            $this->productHasCollections[] = $productHasCollection;
            $productHasCollection->setProduct($this);
        }

        return $this;
    }

    public function removeProductHasCollection(ProductHasCollection $productHasCollection): self
    {
        if ($this->productHasCollections->removeElement($productHasCollection)) {
            // set the owning side to null (unless already changed)
            if ($productHasCollection->getProduct() === $this) {
                $productHasCollection->setProduct(null);
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
            $productHasOccasion->setProduct($this);
        }

        return $this;
    }

    public function removeProductHasOccasion(ProductHasOccasion $productHasOccasion): self
    {
        if ($this->productHasOccasions->removeElement($productHasOccasion)) {
            // set the owning side to null (unless already changed)
            if ($productHasOccasion->getProduct() === $this) {
                $productHasOccasion->setProduct(null);
            }
        }

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

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->addProduct($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            $project->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection|Attribute[]
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setCategory($this);
        }

        return $this;
    }

    public function removeAttribute(Attribute $attribute): self
    {
        if ($this->attributes->removeElement($attribute)) {
            // set the owning side to null (unless already changed)
            if ($attribute->getCategory() === $this) {
                $attribute->setCategory(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }
}
