<?php

namespace PN\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DCollection;

use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\ProductBundle\Entity\Translation\CollectionTranslation;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("collection")
 * @ORM\Entity(repositoryClass="PN\Bundle\ProductBundle\Repository\CollectionRepository")
 */
class Collection implements Translatable, DateTimeInterface
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
     * @ORM\OneToOne(targetEntity="\PN\Bundle\SeoBundle\Entity\Seo", inversedBy="collection", cascade={"persist", "remove"})
     */
    private $seo;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ContentBundle\Entity\Post", inversedBy="collection", cascade={"persist", "remove"})
     */
    private $post;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=45)
     */
    private $title;

    /**
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = true;

    /**
     * @ORM\Column(name="tarteb", type="smallint", nullable=true, options={"default":0}))
     */
    private $tarteb = 0;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Translation\CollectionTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ProductBundle\Entity\ProductHasCollection", mappedBy="collection", cascade={"persist"})
     */
    private $productHasCollections;


    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
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
        $this->productHasCollections = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getTitle();
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

    public function getPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;

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
     * @return DCollection|CollectionTranslation[]
     */
    public function getTranslations(): DCollection
    {
        return $this->translations;
    }

    public function addTranslation(CollectionTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(CollectionTranslation $translation): self
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
     * @return DCollection|ProductHasCollection[]
     */
    public function getProductHasCollections(): DCollection
    {
        return $this->productHasCollections;
    }

    public function addProductHasCollection(ProductHasCollection $productHasCollection): self
    {
        if (!$this->productHasCollections->contains($productHasCollection)) {
            $this->productHasCollections[] = $productHasCollection;
            $productHasCollection->setCollection($this);
        }

        return $this;
    }

    public function removeProductHasCollection(ProductHasCollection $productHasCollection): self
    {
        if ($this->productHasCollections->removeElement($productHasCollection)) {
            // set the owning side to null (unless already changed)
            if ($productHasCollection->getCollection() === $this) {
                $productHasCollection->setCollection(null);
            }
        }

        return $this;
    }

}
