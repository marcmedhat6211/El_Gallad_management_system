<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\Bundle\ProductBundle\Entity\Translation\CategoryTranslation;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\CategoryRepository")
 */
class Category implements Translatable, DateTimeInterface
{

    use VirtualDeleteTrait,
        DateTimeTrait,
        LocaleTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\MediaBundle\Entity\Image", inversedBy="category", cascade={"persist", "remove" })
     */
    protected $image;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\SeoBundle\Entity\Seo", inversedBy="category", cascade={"persist", "remove" })
     */
    private $seo;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     */
    private $parent;


    /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $levelOne;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="concatIds", type="text", nullable=true)
     */
    private $concatIds;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    private $tarteb;

    /**
     * @ORM\Column(type="integer")
     */
    private $depth;

    /**
     * @ORM\Column(name="no_of_images", type="smallint", nullable=true, options={"default" : 0})
     */
    private $noOfImages = 0;

    /**
     * @ORM\Column(name="no_of_products", type="smallint", nullable=true, options={"default" : 0})
     */
    private $noOfProducts = 0;

    /**
     * @ORM\Column(name="no_of_publish_products", type="smallint", nullable=true, options={"default" : 0})
     */
    private $noOfPublishProducts = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="parentConcatIds", type="text", nullable=true)
     */
    private $parentConcatIds;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Translation\CategoryTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true, fetch="EAGER")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"tarteb" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="Attribute", mappedBy="category")
     */
    private $attributes;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

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

    public function __toString()
    {
        return $this->getTitle();
    }

    public function hasChildren()
    {
        $children = $this->getChildren();
        if (count($children) > 0) {
            return true;
        }

        return false;
    }

    public function getChildren($deleted = null)
    {
        return $this->children->filter(function ($children) use ($deleted) {
            if ($children->getDeleted() == $deleted) {
                return $children;
            }
        });
    }

    public function getProducts($deleted = null)
    {
        return $this->products->filter(function ($product) use ($deleted) {
            if ($product->getDeleted() == $deleted) {
                return $product;
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

    public function getConcatIds(): ?string
    {
        return $this->concatIds;
    }

    public function setConcatIds(?string $concatIds): self
    {
        $this->concatIds = $concatIds;

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

    public function getNoOfImages(): ?int
    {
        return $this->noOfImages;
    }

    public function setNoOfImages(?int $noOfImages): self
    {
        $this->noOfImages = $noOfImages;

        return $this;
    }

    public function getNoOfProducts(): ?int
    {
        return $this->noOfProducts;
    }

    public function setNoOfProducts(?int $noOfProducts): self
    {
        $this->noOfProducts = $noOfProducts;

        return $this;
    }

    public function getNoOfPublishProducts(): ?int
    {
        return $this->noOfPublishProducts;
    }

    public function setNoOfPublishProducts(?int $noOfPublishProducts): self
    {
        $this->noOfPublishProducts = $noOfPublishProducts;

        return $this;
    }

    public function getParentConcatIds(): ?string
    {
        return $this->parentConcatIds;
    }

    public function setParentConcatIds(?string $parentConcatIds): self
    {
        $this->parentConcatIds = $parentConcatIds;

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getLevelOne(): ?self
    {
        return $this->levelOne;
    }

    public function setLevelOne(?self $levelOne): self
    {
        $this->levelOne = $levelOne;

        return $this;
    }

    public function addChild(Category $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Category $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CategoryTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(CategoryTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(CategoryTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

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

}
